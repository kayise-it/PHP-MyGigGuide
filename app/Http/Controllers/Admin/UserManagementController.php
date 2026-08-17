<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use App\Rules\UniqueNormalizedName;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
                'totalPages' => ceil($total / $limit),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $query = User::with('roles')
            ->withMax('tokens as last_api_used_at', 'last_used_at');

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

        // Login activity filter (from admin dashboard cards)
        if ($request->filled('login')) {
            match ($request->login) {
                'active_7d' => $query->where('last_login_at', '>=', now()->subDays(7)),
                'active_30d' => $query->where('last_login_at', '>=', now()->subDays(30)),
                'never' => $query->whereNull('last_login_at'),
                default => null,
            };
        }

        // Sorting
        $allowedSorts = ['name', 'email', 'username', 'created_at', 'is_active', 'last_login_at'];
        $sort = $request->get('sort');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'last_login_at') {
            $query->orderByRaw('last_login_at IS NULL')
                ->orderBy('last_login_at', $direction);
        } elseif (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(15)->appends($request->query());

        return view('admin.users.index', [
            'users' => $users,
            'sort' => $sort,
            'direction' => $direction,
            'loginFilter' => $request->get('login'),
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
        $user->load(['roles', 'permissions', 'events', 'venues', 'ratings'])
            ->loadMax('tokens as last_api_used_at', 'last_used_at');

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
            'username' => 'required|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
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

    /**
     * Send a password reset email to the given user.
     */
    public function sendPasswordReset(User $user)
    {
        $token = Str::random(64);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $token));

            return redirect()->route('admin.users.edit', $user)
                ->with('success', 'Password reset email sent to '.$user->email.'.');
        } catch (\Exception $e) {
            Log::error('Admin send password reset failed: '.$e->getMessage());

            return redirect()->route('admin.users.edit', $user)
                ->with('error', 'Failed to send password reset email. Please try again.');
        }
    }

    public function destroy(Request $request, User $user)
    {
        if ((int) auth()->id() === (int) $user->id) {
            return $this->destroyResponse($request, false, 'You cannot delete your own account from admin.');
        }

        DB::beginTransaction();
        try {
            $this->detachUserRelationships($user);
            $user->delete();

            DB::commit();

            return $this->destroyResponse($request, true, 'User deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin user delete failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return $this->destroyResponse(
                $request,
                false,
                'Unable to delete user. Please resolve linked records first. '.$e->getMessage()
            );
        }
    }

    /**
     * Detach or clear links so the users row can be removed safely.
     */
    private function detachUserRelationships(User $user): void
    {
        $user->loadMissing(['artist', 'organiser']);

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

        if (method_exists($user, 'ratings')) {
            $user->ratings()->delete();
        }

        if (method_exists($user, 'ownedVenues')) {
            $user->ownedVenues()->detach();
        }

        $user->tokens()->delete();

        $user->roles()->detach();
        $user->permissions()->detach();

        foreach ([Artist::class, Venue::class, Organiser::class, Event::class] as $modelClass) {
            $modelClass::query()
                ->where('pending_claim_user_id', $user->id)
                ->update(['pending_claim_user_id' => null, 'pending_claim_at' => null]);
        }

        Venue::query()
            ->where('user_id', $user->id)
            ->each(function (Venue $venue) use ($user) {
                $venue->user_id = null;

                if ($venue->owner_type === User::class && (int) $venue->owner_id === (int) $user->id) {
                    $venue->owner_id = null;
                    $venue->owner_type = null;
                }

                $venue->save();
            });

        Event::query()
            ->where('owner_type', User::class)
            ->where('owner_id', $user->id)
            ->update(['owner_id' => null, 'owner_type' => null]);

        if ($user->artist) {
            $user->artist()->update(['user_id' => null]);
        }

        if ($user->organiser) {
            $user->organiser()->update(['user_id' => null]);
        }
    }

    private function destroyResponse(Request $request, bool $success, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $success ? 200 : 422);
        }

        return $success
            ? redirect()->route('admin.users.index')->with('success', $message)
            : back()->with('error', $message);
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
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
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
