<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use App\Rules\UniqueNormalizedName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VenueManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Venue::with('user');

        if ($request->has('search') && $request->search) {
            $rawSearch = trim((string) $request->search);
            // Multi-word search on venue NAME only; all words must appear in name
            $terms = preg_split('/\s+/', $rawSearch, -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($outer) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%'.$term.'%';
                    $outer->where('name', 'like', $like);
                }
            });
        }

        // Handle per page parameter (WordPress style)
        $perPage = $request->input('per_page', 15);
        $perPage = in_array($perPage, [15, 30, 50, 100]) ? $perPage : 15;

        $venues = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        return view('admin.venues.index', compact('venues'));
    }

    public function create()
    {
        return view('admin.venues.create');
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', UniqueNormalizedName::forVenue()],
            'address' => 'required|string',
            'city' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'main_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name', 'address', 'capacity', 'latitude', 'longitude',
        ]);

        // Map city -> location (DB column)
        if ($request->filled('city')) {
            $data['location'] = $request->string('city');
        }

        // Contact email - required by DB (unique, not null). Generate a safe default if absent.
        $contactEmail = $request->input('contact_email');
        if (! $contactEmail) {
            $base = Str::slug($data['name'] ?? 'venue');
            $contactEmail = $base.'+'.time().'@example.local';
        }
        $data['contact_email'] = $contactEmail;

        // Required relational fields
        $userId = Auth::check() ? (int) Auth::id() : 1;
        $data['user_id'] = $userId;
        $data['owner_id'] = $userId;
        $data['owner_type'] = \App\Models\User::class;

        // Ensure the owner's settings (including folder_name) exist before saving uploads
        $owner = User::find($userId);
        if ($owner) {
            $owner->getOrCreateFolderSettings();
        }

        // Handle main picture
        if ($request->hasFile('main_picture')) {
            $baseFolder = $owner ? $owner->getFolderPath() : 'users/unknown';
            $data['main_picture'] = $request->file('main_picture')->store($baseFolder.'/venues/main_pictures', 'public');
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            $baseFolder = $owner ? $owner->getFolderPath() : 'users/unknown';
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($baseFolder.'/venues/gallery', 'public');
            }
            $data['venue_gallery'] = $galleryPaths;
        }

        $venue = Venue::create($data);

        // Handle venue owners if provided (from the venue owner management component)
        if ($request->has('venue_owners') && is_array($request->venue_owners)) {
            foreach ($request->venue_owners as $userId => $role) {
                $user = User::find($userId);
                if ($user) {
                    // Check if relationship already exists
                    if (!$venue->owners()->where('user_id', $userId)->exists()) {
                        $venue->owners()->attach($userId, ['role' => $role]);
                    }
                }
            }
        }

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue created successfully.');
    }

    public function show(Venue $venue)
    {
        $venue->load(['user', 'owners']);

        return view('admin.venues.show', compact('venue'));
    }

    public function edit(Venue $venue)
    {
        return view('admin.venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {

        $venue->loadMissing('user');
        $owner = $venue->user;

        $rules = [
            'name' => ['required', 'string', 'max:255', UniqueNormalizedName::forVenue($venue->id)],
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'main_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
        ];

        if ($owner) {
            $rules['owner_email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($owner->id),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name', 'description', 'address', 'city', 'capacity', 'contact_email', 'contact_phone', 'latitude', 'longitude',
        ]);

        // Handle main picture
        if ($request->hasFile('main_picture')) {
            if ($venue->main_picture && Storage::disk('public')->exists($venue->main_picture)) {
                Storage::disk('public')->delete($venue->main_picture);
            }
            $data['main_picture'] = $request->file('main_picture')->store('venues/main_pictures', 'public');
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            // delete old gallery files if stored as array of paths
            if (is_array($venue->venue_gallery)) {
                foreach ($venue->venue_gallery as $path) {
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store('venues/gallery', 'public');
            }
            $data['venue_gallery'] = $galleryPaths;
        }

        $venue->update($data);

        // Only update owner email if explicitly provided, different, and not accidentally the venue's contact_email
        if ($owner && $request->filled('owner_email')) {
            $newEmail = trim($request->input('owner_email'));

            // Safety check: Don't update if the new email matches the venue's contact_email
            // This prevents accidental overwrites from form autofill or state issues
            if ($newEmail === $venue->contact_email) {
                \Log::warning('Prevented owner email update - matched venue contact_email', [
                    'venue_id' => $venue->id,
                    'owner_id' => $owner->id,
                    'attempted_email' => $newEmail,
                ]);
            } elseif ($newEmail !== $owner->email && !empty($newEmail)) {
                if (! auth()->check() || ! auth()->user()->hasRole('superuser')) {
                    abort(403, 'Only superusers may update owner email addresses.');
                }

                $owner->forceFill([
                    'email' => $newEmail,
                    'email_verified_at' => null,
                ])->save();
            }
        }

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue updated successfully.');
    }

    public function destroy(Venue $venue)
    {
        $venue->delete();

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue deleted successfully.');
    }

    public function toggleStatus(Venue $venue)
    {
        $venue->update(['is_active' => ! $venue->is_active]);

        return back()->with('success', 'Venue status updated successfully.');
    }

    public function importVenues(Request $request)
    {
        try {
            // Check if mapping file exists
            $mappingPath = public_path('venues_to_upload/venue_mapping.json');
            if (!file_exists($mappingPath)) {
                return back()->with('error', 'Venue mapping file not found. Please ensure the Excel file and venue folders are uploaded.');
            }

            // Run the import command
            Artisan::call('venues:import', [
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();

            // Check if successful
            if (str_contains($output, 'Successfully imported')) {
                preg_match('/Successfully imported (\d+) venues/', $output, $matches);
                $count = $matches[1] ?? 'some';
                return redirect()->route('admin.venues.index')
                    ->with('success', "Successfully imported {$count} venues from Excel spreadsheet!");
            }

            return back()->with('info', 'Import completed. Check the venue list for results.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        // Log the request for debugging
        \Log::info('Bulk action request received', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'Unknown',
            'bulk_action' => $request->input('bulk_action'),
            'venue_ids' => $request->input('venue_ids'),
            'all_input' => $request->all(),
        ]);

        $request->validate([
            'bulk_action' => 'required|string',
            'venue_ids' => 'required|array|min:1',
            'venue_ids.*' => 'exists:venues,id',
        ]);

        $action = $request->input('bulk_action');
        $venueIds = $request->input('venue_ids');
        $count = count($venueIds);

        \Log::info('Bulk action validated', [
            'action' => $action,
            'venue_count' => $count,
            'venue_ids' => $venueIds,
        ]);

        switch ($action) {
            case 'delete':
                // Delete selected venues (handle foreign key constraints)
                try {
                    // First, delete related events
                    \DB::table('events')->whereIn('venue_id', $venueIds)->delete();
                    
                    // Then delete venues
                    Venue::whereIn('id', $venueIds)->delete();
                    
                    \Log::info('Bulk delete completed', [
                        'deleted_count' => $count,
                        'venue_ids' => $venueIds,
                    ]);
                    return redirect()->route('admin.venues.index')
                        ->with('success', "Successfully deleted {$count} venue(s) and their related events.");
                        
                } catch (\Exception $e) {
                    \Log::error('Bulk delete failed', [
                        'error' => $e->getMessage(),
                        'venue_ids' => $venueIds,
                    ]);
                    return redirect()->route('admin.venues.index')
                        ->with('error', "Failed to delete venues: " . $e->getMessage());
                }

            case 'export':
                return $this->export($request);

            default:
                \Log::warning('Invalid bulk action attempted', ['action' => $action]);
                return back()->with('error', 'Invalid bulk action selected.');
        }
    }

    /**
     * Export venues to CSV
     */
    public function export(Request $request)
    {
        $query = Venue::with('user');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $rawSearch = trim((string) $request->search);
            $terms = preg_split('/\s+/', $rawSearch, -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($outer) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%'.$term.'%';
                    $outer->where('name', 'like', $like);
                }
            });
        }

        $venues = $query->orderBy('created_at', 'desc')->get();

        $filename = 'venues_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($venues) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Description',
                'Address',
                'City',
                'Capacity',
                'Contact Email',
                'Phone Number',
                'Website',
                'Latitude',
                'Longitude',
                'User ID',
                'User Email',
                'User Name',
                'Created At',
                'Updated At'
            ]);

            // CSV Data
            foreach ($venues as $venue) {
                fputcsv($file, [
                    $venue->id,
                    $venue->name,
                    $venue->description ?? '',
                    $venue->address ?? '',
                    $venue->city ?? '',
                    $venue->capacity ?? '',
                    $venue->contact_email ?? '',
                    $venue->phone_number ?? '',
                    $venue->website ?? '',
                    $venue->latitude ?? '',
                    $venue->longitude ?? '',
                    $venue->user_id ?? '',
                    $venue->user->email ?? '',
                    $venue->user->name ?? '',
                    $venue->created_at->format('Y-m-d H:i:s'),
                    $venue->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download a CSV template for venue import.
     *
     * Uses the same header structure as the export so that
     * uploaded CSV files match the expected column order.
     */
    public function downloadImportTemplate()
    {
        $filename = 'venues_import_template.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // CSV Headers (keep in sync with export())
            fputcsv($file, [
                'ID',
                'Name',
                'Description',
                'Address',
                'City',
                'Capacity',
                'Contact Email',
                'Phone Number',
                'Website',
                'Latitude',
                'Longitude',
                'User ID',
                'User Email',
                'User Name',
                'Created At',
                'Updated At',
            ]);

            // Optionally provide an empty example row
            fputcsv($file, [
                '',        // ID (leave blank for new venues)
                '',        // Name (required)
                '',        // Description
                '',        // Address (required)
                '',        // City
                '',        // Capacity
                '',        // Contact Email (auto-generated if blank)
                '',        // Phone Number
                '',        // Website
                '',        // Latitude
                '',        // Longitude
                '',        // User ID (optional existing admin/owner user)
                '',        // User Email (ignored on import)
                '',        // User Name  (ignored on import)
                '',        // Created At (ignored on import)
                '',        // Updated At (ignored on import)
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import venues from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Read CSV file
        if (($handle = fopen($path, 'r')) !== false) {
            // Skip header row
            $headers = fgetcsv($handle);
            
            $rowNum = 1; // Start at 1 since we skipped header
            
            while (($data = fgetcsv($handle)) !== false) {
                $rowNum++;
                
                // Map CSV columns (adjust indices based on export format)
                $rowData = [
                    'id' => $data[0] ?? null,
                    'name' => trim($data[1] ?? ''),
                    'description' => trim($data[2] ?? ''),
                    'address' => trim($data[3] ?? ''),
                    'city' => trim($data[4] ?? ''),
                    'capacity' => !empty($data[5]) ? (int)$data[5] : null,
                    'contact_email' => trim($data[6] ?? ''),
                    'phone_number' => trim($data[7] ?? ''),
                    'website' => trim($data[8] ?? ''),
                    'latitude' => !empty($data[9]) ? (float)$data[9] : null,
                    'longitude' => !empty($data[10]) ? (float)$data[10] : null,
                    'user_id' => !empty($data[11]) ? (int)$data[11] : null,
                ];

                // Validate required fields
                if (empty($rowData['name'])) {
                    $errors[] = "Row {$rowNum}: Name is required";
                    $skipped++;
                    continue;
                }

                if (empty($rowData['address'])) {
                    $errors[] = "Row {$rowNum}: Address is required";
                    $skipped++;
                    continue;
                }

                // Check if venue exists (by ID or name)
                $venue = null;
                if (!empty($rowData['id'])) {
                    $venue = Venue::find($rowData['id']);
                }
                
                if (!$venue) {
                    $venue = Venue::where('name', $rowData['name'])
                        ->where('address', $rowData['address'])
                        ->first();
                }

                // Validate user_id if provided
                if (!empty($rowData['user_id'])) {
                    $user = User::find($rowData['user_id']);
                    if (!$user) {
                        $errors[] = "Row {$rowNum}: User ID {$rowData['user_id']} not found";
                        $skipped++;
                        continue;
                    }
                } else {
                    // Default to current admin user
                    $rowData['user_id'] = Auth::id() ?? 1;
                }

                // Generate contact_email if missing
                if (empty($rowData['contact_email'])) {
                    $base = Str::slug($rowData['name']);
                    $rowData['contact_email'] = $base . '+' . time() . rand(1000, 9999) . '@example.local';
                }

                // Set owner fields
                $rowData['owner_id'] = $rowData['user_id'];
                $rowData['owner_type'] = \App\Models\User::class;

                try {
                    if ($venue) {
                        // Update existing venue
                        $venue->update($rowData);
                    } else {
                        // Create new venue
                        Venue::create($rowData);
                    }
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                    $skipped++;
                }
            }
            
            fclose($handle);
        }

        $message = "Import completed: {$imported} venues imported";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped";
        }
        if (!empty($errors)) {
            $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= " (and " . (count($errors) - 10) . " more)";
            }
        }

        return redirect()->route('admin.venues.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Add a user to venue with a specific role (owner, co-owner, manager)
     */
    public function addRole(Request $request, Venue $venue)
    {
        // Only superusers can manage venue roles
        if (!auth()->check() || !auth()->user()->hasRole('superuser')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Only superusers may manage venue roles.'], 403);
            }
            abort(403, 'Only superusers may manage venue roles.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:primary,co_owner,manager',
        ]);

        $userId = $request->input('user_id');
        $role = $request->input('role');

        // Check if user already has a role for this venue
        $existingRole = $venue->owners()->where('user_id', $userId)->first();
        
        if ($existingRole) {
            // Update existing role
            $venue->owners()->updateExistingPivot($userId, [
                'role' => $role,
                'added_by_user_id' => auth()->id(),
                'added_at' => now(),
            ]);
            $message = 'User role updated successfully.';
        } else {
            // Add new role
            $venue->owners()->attach($userId, [
                'role' => $role,
                'added_by_user_id' => auth()->id(),
                'added_at' => now(),
            ]);
            $message = 'User added to venue successfully.';
        }

        // If setting as primary, ensure only one primary owner
        if ($role === 'primary') {
            $otherPrimaryOwners = $venue->owners()
                ->where('user_id', '!=', $userId)
                ->wherePivot('role', 'primary')
                ->get();
            
            foreach ($otherPrimaryOwners as $otherOwner) {
                $venue->owners()->updateExistingPivot($otherOwner->id, [
                    'role' => 'co_owner',
                    'added_by_user_id' => auth()->id(),
                    'added_at' => now(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'success' => true]);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a user's role from venue
     */
    public function removeRole(Request $request, Venue $venue)
    {
        // Only superusers can manage venue roles
        if (!auth()->check() || !auth()->user()->hasRole('superuser')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Only superusers may manage venue roles.'], 403);
            }
            abort(403, 'Only superusers may manage venue roles.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->input('user_id');

        // Don't allow removing the last primary owner if there are other owners
        $isPrimary = $venue->owners()
            ->where('user_id', $userId)
            ->wherePivot('role', 'primary')
            ->exists();

        if ($isPrimary && $venue->owners()->count() > 1) {
            $message = 'Cannot remove the last primary owner. Please assign another primary owner first.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        $venue->owners()->detach($userId);

        $message = 'User removed from venue successfully.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'success' => true]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update a user's role for a venue
     */
    public function updateRole(Request $request, Venue $venue)
    {
        // Only superusers can manage venue roles
        if (!auth()->check() || !auth()->user()->hasRole('superuser')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Only superusers may manage venue roles.'], 403);
            }
            abort(403, 'Only superusers may manage venue roles.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:primary,co_owner,manager',
        ]);

        $userId = $request->input('user_id');
        $role = $request->input('role');

        // Check if user has a role for this venue
        if (!$venue->owners()->where('user_id', $userId)->exists()) {
            $message = 'User does not have a role for this venue.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Update role
        $venue->owners()->updateExistingPivot($userId, [
            'role' => $role,
            'added_by_user_id' => auth()->id(),
            'added_at' => now(),
        ]);

        // If setting as primary, ensure only one primary owner
        if ($role === 'primary') {
            $otherPrimaryOwners = $venue->owners()
                ->where('user_id', '!=', $userId)
                ->wherePivot('role', 'primary')
                ->get();
            
            foreach ($otherPrimaryOwners as $otherOwner) {
                $venue->owners()->updateExistingPivot($otherOwner->id, [
                    'role' => 'co_owner',
                    'added_by_user_id' => auth()->id(),
                    'added_at' => now(),
                ]);
            }
        }

        $message = 'User role updated successfully.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'success' => true]);
        }

        return back()->with('success', $message);
    }
}
