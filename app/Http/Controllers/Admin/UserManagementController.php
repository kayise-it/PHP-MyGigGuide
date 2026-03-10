<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\UniqueNormalizedName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Search users for the user selector component
     */
    public function search(Request $request)
    {
        $query = User::with(['roles']);

        // Handle search term
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('username', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Handle role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Handle status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Default ordering
        $query->orderBy('name');

        // Pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $total = $query->count();
        $users = $query->offset($offset)->limit($limit)->get();

        // Add role information and format for frontend
        $users = $users->map(function ($user) {
            $user->role_name = $user->roles->first() ? ucfirst($user->roles->first()->name) : 'User';
            return $user;
        });

        return response()->json([
            'users' => $users,
            'pagination' => [
                'page' => (int) $page,
                'limit' => (int) $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Sorting
        $allowedSorts = ['name', 'email', 'username', 'created_at', 'is_active'];
        $sort = $request->get('sort');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(15)->appends($request->query());

        return view('admin.users.index', [
            'users' => $users,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create()
    {
        $roles = \Laratrust\Models\Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', UniqueNormalizedName::forUser()],
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->addRole($request->role);

        // Ensure domain profiles exist for assigned roles (artist, organiser, etc.)
        if (method_exists($user, 'ensureRoleProfiles')) {
            $user->ensureRoleProfiles();
        }

        // Initialize settings and create user folder structure
        if (method_exists($user, 'getOrCreateFolderSettings')) {
            $user->getOrCreateFolderSettings();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(Request $request, User $user)
    {
        $user->load(['roles', 'permissions', 'events', 'venues', 'ratings']);

        if ($request->ajax()) {
            return view('admin.users.show-modal', compact('user'));
        }

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = \Laratrust\Models\Role::all();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', UniqueNormalizedName::forUser($user->id)],
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            // Admin edit form allows multiple roles + direct permissions
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Sync roles if provided (keep existing behaviour if not)
        if ($request->filled('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        // Sync direct permissions if provided
        if ($request->has('permissions')) {
            $user->syncPermissions($request->input('permissions', []));
        }

        // Ensure domain profiles exist for assigned roles after update
        if (method_exists($user, 'ensureRoleProfiles')) {
            $user->ensureRoleProfiles();
        }

        // Ensure settings exist after updates
        if (method_exists($user, 'getOrCreateFolderSettings')) {
            $user->getOrCreateFolderSettings();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        \DB::beginTransaction();
        try {
            // #region agent log
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode([
                'location'    => 'UserManagementController.php:destroy:entry',
                'message'     => 'Admin user delete entry',
                'data'        => [
                    'target_user_id'    => $user->id,
                    'target_user_email' => $user->email,
                    'has_artist'        => (bool) ($user->artist ?? null),
                    'has_organiser'     => (bool) ($user->organiser ?? null),
                ],
                'timestamp'  => round(microtime(true) * 1000),
                'sessionId'  => 'debug-session',
                'runId'      => 'user-delete',
                'hypothesisId' => 'DEL1',
            ]) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion

            // Detach favorites
            if (method_exists($user, 'favoriteEvents')) {
                $user->favoriteEvents()->detach();
            }
            if (method_exists($user, 'favoriteVenues')) {
                $user->favoriteVenues()->detach();
            }
            if (method_exists($user, 'favoriteArtists')) {
                $user->favoriteArtists()->detach();
            }
            if (method_exists($user, 'favoriteOrganisers')) {
                $user->favoriteOrganisers()->detach();
            }

            // Delete ratings made by the user
            if (method_exists($user, 'ratings')) {
                $user->ratings()->delete();
            }

            // Null any pending artist claims referencing this user
            \App\Models\Artist::where('pending_claim_user_id', $user->id)
                ->update(['pending_claim_user_id' => null, 'pending_claim_at' => null]);

            // Mark any venues owned by this user as UNCLAIMED by nulling user_id/owner links
            $ownedVenues = \App\Models\Venue::where('user_id', $user->id)->get();
            foreach ($ownedVenues as $venue) {
                $venue->user_id = null;

                // If this user is set as polymorphic owner, clear that too
                if ($venue->owner_type === \App\Models\User::class && $venue->owner_id === $user->id) {
                    $venue->owner_id = null;
                    $venue->owner_type = null;
                }

                $venue->save();
            }

            // If the user has an artist profile, gracefully detach without deleting related events
            if (method_exists($user, 'artist') && $user->artist) {
                $user->artist()->update(['user_id' => null]);
            }

            // If the user has an organiser profile, mark it unclaimed by nulling user_id
            if (method_exists($user, 'organiser') && $user->organiser) {
                $user->organiser()->update(['user_id' => null]);
            }

            // Finally delete the user
            $user->delete();

            \DB::commit();

            // #region agent log
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode([
                'location'    => 'UserManagementController.php:destroy:success',
                'message'     => 'Admin user delete succeeded',
                'data'        => [
                    'target_user_id'    => $user->id,
                    'target_user_email' => $user->email,
                ],
                'timestamp'  => round(microtime(true) * 1000),
                'sessionId'  => 'debug-session',
                'runId'      => 'user-delete',
                'hypothesisId' => 'DEL1',
            ]) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Throwable $e) {
            \DB::rollBack();

            // #region agent log
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode([
                'location'    => 'UserManagementController.php:destroy:error',
                'message'     => 'Admin user delete failed',
                'data'        => [
                    'target_user_id'    => $user->id,
                    'target_user_email' => $user->email,
                    'exception_class'   => get_class($e),
                    'exception_message' => $e->getMessage(),
                ],
                'timestamp'  => round(microtime(true) * 1000),
                'sessionId'  => 'debug-session',
                'runId'      => 'user-delete',
                'hypothesisId' => 'DEL2',
            ]) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion

            return back()->with('error', 'Unable to delete user. Please resolve linked records first. '.$e->getMessage());
        }
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'User status updated successfully.');
    }

    /**
     * Mark user's email as verified.
     */
    public function verifyEmail(User $user)
    {
        $user->forceFill(['email_verified_at' => now()])->save();

        return back()->with('success', 'Email marked as verified.');
    }

    /**
     * Mark user's email as unverified.
     */
    public function unverifyEmail(User $user)
    {
        $user->forceFill(['email_verified_at' => null])->save();

        return back()->with('success', 'Email marked as unverified.');
    }

    /**
     * Update the user's email address (super admin only; supports AJAX).
     */
    public function updateEmail(Request $request, User $user)
    {
        if (! auth()->check() || ! auth()->user()->hasRole('superuser')) {
            abort(403, 'Only superusers may update email addresses.');
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'sync_related' => 'sometimes|boolean',
        ]);

        $oldEmail = $user->email;
        $user->email = $validated['email'];
        // Email is changed manually; mark unverified to maintain integrity unless explicitly verified later
        $user->email_verified_at = null;
        $user->save();

        if ($request->boolean('sync_related')) {
            // Sync artist/organiser contact emails if present
            if ($user->artist) {
                $user->artist->update(['contact_email' => $user->email]);
            }
            if ($user->organiser) {
                $user->organiser->update(['contact_email' => $user->email]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'email' => $user->email]);
        }

        return back()->with('success', 'Email updated successfully.');
    }
}
