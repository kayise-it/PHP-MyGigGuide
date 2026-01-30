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
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:index:entry','message'=>'index method called','data'=>['type'=>$request->get('type','all'),'search'=>$request->get('search'),'isAjax'=>$request->ajax()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H1,H2'])."\n", FILE_APPEND);
        // #endregion
        
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
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:index:counts','message'=>'Got unclaimed counts','data'=>['counts'=>$counts],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        // Build query based on type
        $unclaimed = $this->getUnclaimedQuery($type, $search);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:index:queryResult','message'=>'Query executed','data'=>['totalItems'=>$unclaimed->total(),'currentPage'=>$unclaimed->currentPage(),'perPage'=>$unclaimed->perPage()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        // For AJAX requests (search), return partial
        if ($request->ajax()) {
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:index:ajaxReturn','message'=>'Returning AJAX partial view','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
            // #endregion
            return view('admin.unclaimed._table', compact('unclaimed', 'type'));
        }

        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:index:fullReturn','message'=>'Returning full view','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
        // #endregion
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
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:edit:entry','message'=>'edit method called','data'=>['type'=>$type,'id'=>$id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H1,H2'])."\n", FILE_APPEND);
        // #endregion
        
        $entity = $this->findEntity($type, $id);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:edit:entityFound','message'=>'Entity lookup result','data'=>['found'=>$entity !== null,'isUnclaimed'=>$entity?->isUnclaimed(),'entityId'=>$entity?->id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        if (!$entity || !$entity->isUnclaimed()) {
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:edit:abort','message'=>'Aborting 404 - entity not found or not unclaimed','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
            // #endregion
            abort(404, 'Unclaimed item not found');
        }

        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:edit:returnView','message'=>'Returning edit view','data'=>['viewName'=>"admin.unclaimed.edit-{$type}"],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
        // #endregion
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
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:entry','message'=>'update method called','data'=>['type'=>$type,'id'=>$id,'method'=>$request->method(),'hasData'=>!empty($request->all())],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H1,H2'])."\n", FILE_APPEND);
        // #endregion
        
        $entity = $this->findEntity($type, $id);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:entityFound','message'=>'Entity lookup result','data'=>['found'=>$entity !== null,'isUnclaimed'=>$entity?->isUnclaimed()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        if (!$entity || !$entity->isUnclaimed()) {
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:abort','message'=>'Aborting 404 - entity not found or not unclaimed','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
            // #endregion
            abort(404, 'Unclaimed item not found');
        }

        // Validate based on type
        $validator = $this->getValidator($request, $type);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:validation','message'=>'Validation result','data'=>['fails'=>$validator->fails(),'errors'=>$validator->fails() ? $validator->errors()->toArray() : []],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H3'])."\n", FILE_APPEND);
        // #endregion

        if ($validator->fails()) {
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:validationFailed','message'=>'Validation failed, returning with errors','data'=>['errors'=>$validator->errors()->toArray()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H3'])."\n", FILE_APPEND);
            // #endregion
            return back()->withErrors($validator)->withInput();
        }

        // Get update data based on type
        $data = $this->getUpdateData($request, $type, $entity);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:beforeUpdate','message'=>'About to update entity','data'=>['dataKeys'=>array_keys($data)],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        $entity->update($data);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:afterUpdate','message'=>'Entity updated successfully','data'=>['entityId'=>$entity->id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:update:redirect','message'=>'Redirecting after successful update','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
        // #endregion
        return redirect()->route('admin.unclaimed.index', ['type' => $type])
            ->with('success', ucfirst($type) . ' updated successfully.');
    }

    /**
     * Delete an unclaimed item.
     */
    public function destroy(string $type, int $id)
    {
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:destroy:entry','message'=>'destroy method called','data'=>['type'=>$type,'id'=>$id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H1,H2'])."\n", FILE_APPEND);
        // #endregion
        
        $entity = $this->findEntity($type, $id);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:destroy:entityFound','message'=>'Entity lookup result','data'=>['found'=>$entity !== null,'isUnclaimed'=>$entity?->isUnclaimed()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        if (!$entity || !$entity->isUnclaimed()) {
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:destroy:abort','message'=>'Aborting 404 - entity not found or not unclaimed','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
            // #endregion
            abort(404, 'Unclaimed item not found');
        }

        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:destroy:beforeDelete','message'=>'About to delete entity','data'=>['entityId'=>$entity->id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion
        
        $entity->delete();
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:destroy:afterDelete','message'=>'Entity deleted successfully','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:destroy:redirect','message'=>'Redirecting after successful delete','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
        // #endregion
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
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:entry','message'=>'linkToUser called','data'=>['type'=>$type,'id'=>$id,'user_id'=>$request->user_id,'force_replace'=>$request->force_replace,'isAjax'=>$request->ajax(),'wantsJson'=>$request->wantsJson()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
        // #endregion
        
        $entity = $this->findEntity($type, $id);
        
        if (!$entity) {
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:entityNotFound','message'=>'Entity not found','data'=>['type'=>$type,'id'=>$id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
            // #endregion
            abort(404, 'Entity not found');
        }
        
        // Refresh entity to get latest state from database (important for race conditions)
        $entity->refresh();
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:entityCheck','message'=>'Entity found and unclaimed check','data'=>['entityFound'=>$entity !== null,'entityId'=>$entity->id,'isUnclaimed'=>$entity->isUnclaimed(),'currentOwnerId'=>$entity->getOwnerUserId()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H1,H4'])."\n", FILE_APPEND);
        // #endregion

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
            
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:oneToOneCheck','message'=>'Checking 1-to-1 relationship constraint','data'=>['type'=>$type,'userId'=>$user->id,'hasExisting'=>$existing !== null,'existingId'=>$existing?->id,'newEntityId'=>$entity->id,'forceReplace'=>$forceReplace],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H3'])."\n", FILE_APPEND);
            // #endregion
            
            // If user already has this type of entity and it's not the same entity, and not forcing replace
            if ($existing && $existing->id !== $entity->id && !$forceReplace) {
                // Return conflict response instead of 404
                // #region agent log
                file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:oneToOneConflict','message'=>'1-to-1 conflict detected before linking','data'=>['existingId'=>$existing->id,'newEntityId'=>$entity->id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H3'])."\n", FILE_APPEND);
                // #endregion
                
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
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:abort','message'=>'Aborting - entity not unclaimed','data'=>['entityFound'=>$entity !== null,'isUnclaimed'=>$entity->isUnclaimed(),'currentOwnerId'=>$entity->getOwnerUserId()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
            // #endregion
            abort(404, 'Unclaimed item not found');
        }
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:beforeLink','message'=>'About to call claimService->linkToUser','data'=>['userId'=>$user->id,'userName'=>$user->name,'forceReplace'=>$forceReplace,'entityType'=>$type],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2,H3'])."\n", FILE_APPEND);
        // #endregion
        
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
            
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:beforeServiceCall','message'=>'Entity state before service call','data'=>['entityId'=>$entity->id,'entityType'=>$type,'isUnclaimed'=>$entity->isUnclaimed(),'currentOwnerId'=>$entity->getOwnerUserId()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H1,H4'])."\n", FILE_APPEND);
            // #endregion
            
            $this->claimService->linkToUser($entity, $user, $forceReplace);
            
            \DB::commit();
            
            // #region agent log
            $entity->refresh();
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:afterServiceCall','message'=>'Entity state after service call','data'=>['entityId'=>$entity->id,'isUnclaimed'=>$entity->isUnclaimed(),'ownerId'=>$entity->getOwnerUserId(),'userId'=>$user->id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H1,H4'])."\n", FILE_APPEND);
            // #endregion
            
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:success','message'=>'linkToUser succeeded without exception','data'=>['responseType'=>$request->ajax() ? 'ajax' : 'redirect'],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2,H5'])."\n", FILE_APPEND);
            // #endregion
        } catch (\App\Exceptions\UserAlreadyHasEntityException $e) {
            \DB::rollBack();
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:conflict','message'=>'UserAlreadyHasEntityException caught','data'=>$e->getConflictData(),'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
            // #endregion
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
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:linkToUser:exception','message'=>'Unexpected exception caught','data'=>['message'=>$e->getMessage(),'class'=>get_class($e),'file'=>$e->getFile(),'line'=>$e->getLine()],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
            // #endregion
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
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:checkLinkConflict:entry','message'=>'checkLinkConflict called','data'=>['type'=>$type,'id'=>$id,'user_id'=>$request->user_id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H2'])."\n", FILE_APPEND);
        // #endregion
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $entity = $this->findEntity($type, $id);

        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }

        $user = User::findOrFail($request->user_id);
        $conflict = $this->claimService->checkLinkConflict($user, $type, $id);

        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:checkLinkConflict:result','message'=>'Conflict check result','data'=>['hasConflict'=>$conflict !== null,'conflict'=>$conflict],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H3'])."\n", FILE_APPEND);
        // #endregion

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
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:findEntity:entry','message'=>'findEntity called','data'=>['type'=>$type,'id'=>$id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion
        
        $modelClass = $this->claimService->getModelClass($type);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:findEntity:modelClass','message'=>'Model class lookup','data'=>['modelClass'=>$modelClass],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion

        if (!$modelClass) {
            // #region agent log
            file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:findEntity:noModelClass','message'=>'No model class found for type','data'=>[],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
            // #endregion
            return null;
        }

        $entity = $modelClass::find($id);
        
        // #region agent log
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'UnclaimedController:findEntity:result','message'=>'Entity lookup result','data'=>['found'=>$entity !== null,'entityId'=>$entity?->id],'timestamp'=>round(microtime(true)*1000),'sessionId'=>'debug-session','hypothesisId'=>'H4'])."\n", FILE_APPEND);
        // #endregion
        
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

