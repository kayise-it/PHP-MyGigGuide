<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use App\Services\ClaimService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UnclaimedController extends Controller
{
    protected ClaimService $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * Display all unclaimed items with type filtering.
     */
    public function index(Request $request)
    {
// Validate and sanitize inputs
        $request->validate([
            'type' => 'nullable|in:all,artist,venue,event,organiser',
            'search' => 'nullable|string|max:255',
        ]);
        
        $type = $request->get('type', 'all');
        $search = $request->get('search');
        
        // Sanitize search input to prevent SQL injection
        if ($search) {
            $search = trim($search);
            $search = preg_replace('/[%_]/', '', $search);
        }

        // Get counts for all types
        $counts = $this->claimService->getUnclaimedCounts();
        $counts['all'] = array_sum($counts);
// Build query based on type
        $unclaimed = $this->getUnclaimedQuery($type, $search);
// For AJAX requests (search), return partial
        if ($request->ajax()) {
return view('admin.unclaimed._table', compact('unclaimed', 'type'));
        }
return view('admin.unclaimed.index', compact('unclaimed', 'counts', 'type'));
    }

    /**
     * Get the appropriate query based on type filter.
     */
    protected function getUnclaimedQuery(string $type, ?string $search)
    {
        $perPage = 20;
        $request = request();

        if ($type === 'all') {
            // Combine all types into a unified collection
            return $this->getAllUnclaimedPaginated($search, $perPage, $request);
        }

        $modelClass = $this->claimService->getModelClass($type);
        
        if (!$modelClass) {
            abort(404, 'Invalid type');
        }

        $query = $modelClass::query();

        // Apply unclaimed filter based on type
        if ($type === 'event') {
            $query->whereNull('owner_id');
        } elseif ($type === 'venue') {
            $query->whereNull('user_id')->whereNull('owner_id');
        } else {
            $query->whereNull('user_id');
        }

        // Apply search (searches both name and email)
        if ($search) {
            $this->applySearch($query, $type, $search);
        }

        return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }

    /**
     * Get all unclaimed items from all types, paginated.
     */
    protected function getAllUnclaimedPaginated(?string $search, int $perPage, Request $request)
    {
        // Helper to build query for each type
        $buildQuery = function ($modelClass, string $type) use ($search, $request) {
            $query = $modelClass::query();
            
            // Apply unclaimed filter
            if ($type === 'event') {
                $query->whereNull('owner_id');
            } elseif ($type === 'venue') {
                $query->whereNull('user_id')->whereNull('owner_id');
            } else {
                $query->whereNull('user_id');
            }
            
            // Apply search (searches both name and email)
            if ($search) {
                $this->applySearch($query, $type, $search);
            }
            
            return $query->get()->map(fn($item) => $this->transformForUnified($item, $type));
        };

        // Get all unclaimed entities
        $artists = $buildQuery(Artist::class, 'artist');
        $venues = $buildQuery(Venue::class, 'venue');
        $events = $buildQuery(Event::class, 'event');
        $organisers = $buildQuery(Organiser::class, 'organiser');

        // Merge all items
        $all = $artists->merge($venues)->merge($events)->merge($organisers);

        // Apply status filter to collection (for "all" type, filter after transformation)
        $status = $request->get('status');
        if ($status) {
            $all = $all->filter(function ($item) use ($status) {
                return match ($status) {
                    'pending' => $item->has_pending_claim ?? false,
                    'disputed' => $item->has_dispute ?? false,
                    'unclaimed' => !($item->has_pending_claim ?? false) && !($item->has_dispute ?? false),
                    default => true,
                };
            })->values();
        }

        // Apply default sorting (newest first)
        $all = $all->sortByDesc('created_at')->values();

        // Manual pagination
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        $items = $all->slice($offset, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Transform an entity for unified display.
     */
    protected function transformForUnified(Model $item, string $type): object
    {
        return (object) [
            'id' => $item->id,
            'type' => $type,
            'model' => $item,
            'name' => $item->getDisplayName(),
            'email' => $item->getClaimEmail(),
            'created_at' => $item->created_at,
            'has_pending_claim' => $item->hasPendingClaim(),
            'has_dispute' => $item->hasDisputedClaim(),
            'type_icon' => $item->getTypeIcon(),
            'type_color' => $item->getTypeColorClass(),
            'image' => $this->getEntityImage($item, $type),
        ];
    }

    /**
     * Get the image/avatar for an entity.
     */
    protected function getEntityImage(Model $item, string $type): ?string
    {
        return match ($type) {
            'artist' => $item->profile_picture,
            'venue' => $item->main_picture,
            'event' => $item->poster,
            'organiser' => $item->logo,
            default => null,
        };
    }

    /**
     * Apply search filter based on type (searches both name and email).
     */
    protected function applySearch($query, string $type, string $search)
    {
        // Common search fields: name field and email field for each type
        $nameField = match ($type) {
            'artist' => 'stage_name',
            'venue' => 'name',
            'event' => 'name',
            'organiser' => 'organisation_name',
            default => 'name',
        };

        // Email fields vary by type - only include columns that exist
        $emailFields = match ($type) {
            'artist' => ['contact_email', 'email'], // Artists have both
            'venue' => ['contact_email'], // Venues only have contact_email
            'event' => [], // Events don't have direct email
            'organiser' => ['contact_email'], // Organisers only have contact_email
            default => ['contact_email'],
        };

        // Get table name and check which columns actually exist
        $tableName = $query->getModel()->getTable();
        $columns = Schema::getColumnListing($tableName);
        
        // Filter email fields to only those that exist in the table
        $validEmailFields = array_filter($emailFields, function($field) use ($columns) {
            return in_array($field, $columns);
        });

        $query->where(function ($q) use ($nameField, $validEmailFields, $search, $columns) {
            // Search by name (check if column exists)
            if (in_array($nameField, $columns)) {
                $q->where($nameField, 'like', "%{$search}%");
            }
            
            // Search by email fields that exist
            foreach ($validEmailFields as $emailField) {
                $q->orWhere($emailField, 'like', "%{$search}%");
            }
        });
        
        return $query;
    }

    /**
     * Apply status filter.
     */
    protected function applyStatusFilter($query, string $type, Request $request): void
    {
        $status = $request->get('status');
        
        if (!$status) {
            return;
        }

        // Check if table has required columns before applying filter
        $tableName = $query->getModel()->getTable();
        $columns = Schema::getColumnListing($tableName);
        
        match ($status) {
            'pending' => $this->applyPendingFilter($query, $type, $columns),
            'disputed' => $this->applyDisputedFilter($query, $type, $columns),
            'unclaimed' => $this->applyUnclaimedFilter($query, $type, $columns),
            default => null,
        };
    }

    /**
     * Filter for pending claims.
     */
    protected function applyPendingFilter($query, string $type, array $columns): void
    {
        // Only apply if table has the required columns
        if (in_array('pending_claim_user_id', $columns) && in_array('claim_status', $columns)) {
            $query->whereNotNull('pending_claim_user_id')
                  ->where('claim_status', 'pending');
        }
    }

    /**
     * Filter for disputed claims.
     */
    protected function applyDisputedFilter($query, string $type, array $columns): void
    {
        // Only apply if table has the required columns
        if (in_array('dispute_raised', $columns) || in_array('claim_status', $columns)) {
            $query->where(function ($q) use ($columns) {
                if (in_array('dispute_raised', $columns)) {
                    $q->where('dispute_raised', true);
                }
                if (in_array('claim_status', $columns)) {
                    $q->orWhere('claim_status', 'disputed');
                }
            });
        }
    }

    /**
     * Filter for unclaimed items (no pending claims or disputes).
     */
    protected function applyUnclaimedFilter($query, string $type, array $columns): void
    {
        // Items that are truly unclaimed (no pending claims, no disputes)
        if (in_array('pending_claim_user_id', $columns)) {
            $query->whereNull('pending_claim_user_id');
        }
        if (in_array('dispute_raised', $columns)) {
            $query->where(function ($q) {
                $q->where('dispute_raised', false)
                  ->orWhereNull('dispute_raised');
            });
        }
        if (in_array('claim_status', $columns)) {
            $query->where(function ($q) {
                $q->where('claim_status', 'none')
                  ->orWhereNull('claim_status');
            });
        }
    }

    /**
     * Apply date filters to query.
     */
    protected function applyDateFilters($query, string $type, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }

    /**
     * Apply sorting to query.
     */
    protected function applySorting($query, string $type, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_desc');
        
        match ($sortBy) {
            'created_asc' => $query->orderBy('created_at', 'asc'),
            'created_desc' => $query->orderByDesc('created_at'),
            'name_asc' => $query->orderBy($this->getNameField($type), 'asc'),
            'name_desc' => $query->orderByDesc($this->getNameField($type)),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * Get the name field for a given type.
     */
    protected function getNameField(string $type): string
    {
        return match ($type) {
            'artist' => 'stage_name',
            'venue' => 'name',
            'event' => 'name',
            'organiser' => 'organisation_name',
            default => 'created_at',
        };
    }

    /**
     * Sort a collection of unified items.
     */
    protected function sortCollection($collection, string $sortBy)
    {
        return match ($sortBy) {
            'created_asc' => $collection->sortBy('created_at'),
            'created_desc' => $collection->sortByDesc('created_at'),
            'name_asc' => $collection->sortBy('name'),
            'name_desc' => $collection->sortByDesc('name'),
            default => $collection->sortByDesc('created_at'),
        };
    }

    /**
     * Show the edit form for an unclaimed item.
     */
    public function edit(string $type, int $id)
    {
$entity = $this->findEntity($type, $id);
if (!$entity || !$entity->isUnclaimed()) {
abort(404, 'Unclaimed item not found');
        }
return view("admin.unclaimed.edit-{$type}", [
            'entity' => $entity,
            'type' => $type,
        ]);
    }

    /**
     * Update an unclaimed item.
     */
    public function update(Request $request, string $type, int $id)
    {
$entity = $this->findEntity($type, $id);
if (!$entity || !$entity->isUnclaimed()) {
abort(404, 'Unclaimed item not found');
        }

        // Validate based on type
        $validator = $this->getValidator($request, $type);
if ($validator->fails()) {
return back()->withErrors($validator)->withInput();
        }

        // Get update data based on type
        $data = $this->getUpdateData($request, $type, $entity);
$entity->update($data);

return redirect()->route('admin.unclaimed.index', ['type' => $type])
            ->with('success', ucfirst($type) . ' updated successfully.');
    }

    /**
     * Delete an unclaimed item.
     */
    public function destroy(string $type, int $id)
    {
$entity = $this->findEntity($type, $id);
if (!$entity || !$entity->isUnclaimed()) {
abort(404, 'Unclaimed item not found');
        }
$entity->delete();

return redirect()->route('admin.unclaimed.index', ['type' => $type])
            ->with('success', ucfirst($type) . ' deleted successfully.');
    }

    /**
     * Send a claim invitation email.
     */
    public function sendClaimInvite(string $type, int $id)
    {
        $entity = $this->findEntity($type, $id);

        if (!$entity || !$entity->isUnclaimed()) {
            abort(404, 'Unclaimed item not found');
        }

        $email = $entity->getClaimEmail();

        if (!$email) {
            return back()->with('error', 'No email address found for this ' . $type);
        }

        $success = $this->claimService->sendClaimInvitation($entity);

        if ($success) {
            return back()->with('success', 'Claim invitation email sent to ' . $email);
        }

        return back()->with('error', 'Failed to send claim invitation email');
    }

    /**
     * Send claim invitation emails to multiple selected items (bulk action).
     */
    public function bulkSendClaimEmails(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'required|string|regex:/^(artist|venue|event|organiser)-\d+$/',
        ]);

        $items = $request->input('items', []);
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($items as $item) {
            // Parse "type-id" format
            if (!preg_match('/^(\w+)-(\d+)$/', $item, $matches)) {
                $failed++;
                $errors[] = "Invalid item format: {$item}";
                continue;
            }

            $type = $matches[1];
            $id = (int) $matches[2];

            try {
                $entity = $this->findEntity($type, $id);

                if (!$entity || !$entity->isUnclaimed()) {
                    $failed++;
                    $errors[] = "{$type} #{$id} is not unclaimed or not found";
                    continue;
                }

                $email = $entity->getClaimEmail();

                if (!$email) {
                    $failed++;
                    $errors[] = "{$type} #{$id} has no email address";
                    continue;
                }

                $success = $this->claimService->sendClaimInvitation($entity);

                if ($success) {
                    $sent++;
                } else {
                    $failed++;
                    $errors[] = "Failed to send email to {$email} ({$type} #{$id})";
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Error processing {$type} #{$id}: " . $e->getMessage();
                Log::error("Bulk email error for {$type} #{$id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($items),
            'errors' => $errors,
            'message' => "Successfully sent {$sent} email(s)" . ($failed > 0 ? ". {$failed} failed." : ".")
        ]);
    }

    /**
     * Manually link an entity to a user.
     */
    public function linkToUser(Request $request, string $type, int $id)
    {
$entity = $this->findEntity($type, $id);
        
        if (!$entity) {
abort(404, 'Entity not found');
        }
        
        // Refresh entity to get latest state from database (important for race conditions)
        $entity->refresh();
// Validate request first
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'force_replace' => 'nullable|boolean',
        ]);

        $user = User::findOrFail($request->user_id);
        $forceReplace = $request->boolean('force_replace', false);
        
        // For 1-to-1 relationships (artist, organiser), check if user already has one BEFORE checking if entity is unclaimed
        // This respects the database constraint: User hasOne Artist, User hasOne Organiser
        if (in_array($type, ['artist', 'organiser'])) {
            $existing = $this->claimService->getUserExistingEntity($user, $type);
// If user already has this type of entity and it's not the same entity, and not forcing replace
            if ($existing && $existing->id !== $entity->id && !$forceReplace) {
                // Return conflict response instead of 404
if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'conflict' => true,
                        'data' => [
                            'type' => $type,
                            'existing_id' => $existing->id,
                            'existing_name' => $existing->getDisplayName(),
                            'user_name' => $user->name,
                            'user_id' => $user->id,
                        ],
                    ], 409);
                }
                
                return back()->with('error', "User {$user->name} already has a {$type} profile: {$existing->getDisplayName()}. Use 'Replace & Link' to replace it.");
            }
        }
        
        // Now check if entity is unclaimed (after checking 1-to-1 constraints)
        if (!$entity->isUnclaimed()) {
abort(404, 'Unclaimed item not found');
        }
try {
            // Use database transaction to prevent race conditions
            \DB::beginTransaction();
            
            // Refresh entity to get latest state (prevents stale data issues)
            $entity->refresh();
            
            // Double-check entity is still unclaimed after refresh
            if (!$entity->isUnclaimed()) {
                \DB::rollBack();
                abort(404, 'This item is no longer unclaimed');
            }
$this->claimService->linkToUser($entity, $user, $forceReplace);
            
            \DB::commit();

} catch (\App\Exceptions\UserAlreadyHasEntityException $e) {
            \DB::rollBack();
// For AJAX requests, return JSON with conflict data
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'conflict' => true,
                    'data' => $e->getConflictData(),
                ], 409);
            }
            
            // For regular requests, redirect back with error
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \DB::rollBack();
if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.unclaimed.index', ['type' => $type])
            ->with('success', ucfirst($type) . ' linked to ' . $user->name . ' successfully.');
    }

    /**
     * Check if linking would cause a conflict (API endpoint).
     */
    public function checkLinkConflict(Request $request, string $type, int $id)
    {
$request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $entity = $this->findEntity($type, $id);

        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }

        $user = User::findOrFail($request->user_id);
        $conflict = $this->claimService->checkLinkConflict($user, $type, $id);
$response = [
            'has_conflict' => $conflict !== null,
            'conflict' => $conflict,
        ];
        
        // Ensure user_name is always present in conflict data
        if ($conflict && isset($conflict['user_name'])) {
            $response['conflict']['user_name'] = $conflict['user_name'] ?: ($user->email ?: 'Unknown User');
        } elseif ($conflict && $user) {
            $response['conflict']['user_name'] = $user->name ?: ($user->email ?: 'Unknown User');
        }
        
        return response()->json($response);
    }

    /**
     * Find an entity by type and ID.
     */
    protected function findEntity(string $type, int $id): ?Model
    {
$modelClass = $this->claimService->getModelClass($type);
if (!$modelClass) {
return null;
        }

        $entity = $modelClass::find($id);
return $entity;
    }

    /**
     * Get validator for update based on type.
     */
    protected function getValidator(Request $request, string $type)
    {
        $rules = match ($type) {
            'artist' => [
                'stage_name' => 'required|string|max:255',
                'real_name' => 'nullable|string|max:255',
                'genre' => 'required|string|max:255',
                'bio' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'contact_email' => 'required|email',
                'website' => 'nullable|url',
                'instagram' => 'nullable|url',
                'facebook' => 'nullable|url',
                'twitter' => 'nullable|url',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ],
            'venue' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'city' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'capacity' => 'nullable|integer|min:0',
                'contact_email' => 'required|email',
                'phone_number' => 'nullable|string',
                'website' => 'nullable|url',
                'main_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ],
            'event' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'time' => 'nullable|string',
                'price' => 'nullable|string',
                'ticket_url' => 'nullable|url',
                'venue_id' => 'nullable|exists:venues,id',
                'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ],
            'organiser' => [
                'organisation_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'contact_email' => 'required|email',
                'phone_number' => 'nullable|string',
                'website' => 'nullable|url',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ],
            default => [],
        };

        return Validator::make($request->all(), $rules);
    }

    /**
     * Get update data based on type.
     */
    protected function getUpdateData(Request $request, string $type, Model $entity): array
    {
        $imageField = match ($type) {
            'artist' => 'profile_picture',
            'venue' => 'main_picture',
            'event' => 'poster',
            'organiser' => 'logo',
            default => null,
        };

        // Exclude image field, link_user_id (used only for linking, not updating), and standard form fields
        $excludeFields = [$imageField, 'link_user_id', '_token', '_method'];
        
        // Also exclude user_id/owner_id fields - these should only be set via linkToUser method
        $ownerField = $entity->getOwnerUserIdField();
        $excludeFields[] = $ownerField;

        $data = $request->except($excludeFields);

        // Handle image upload
        if ($imageField && $request->hasFile($imageField)) {
            // Delete old image if exists
            $currentImage = $entity->{$imageField};
            if ($currentImage && Storage::disk('public')->exists($currentImage)) {
                Storage::disk('public')->delete($currentImage);
            }

            // Store new image
            $folder = match ($type) {
                'artist' => 'artists/profile_pictures',
                'venue' => 'venues/pictures',
                'event' => 'events/posters',
                'organiser' => 'organisers/logos',
                default => 'uploads',
            };

            $data[$imageField] = $request->file($imageField)->store($folder, 'public');
        }

        return $data;
    }
}

