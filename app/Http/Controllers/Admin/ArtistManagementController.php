<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\User;
use App\Models\YoutubeVideo;
use App\Rules\UniqueNormalizedName;
use App\Rules\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtistManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Artist::with('user');

        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                // Substring matching across key fields
                $q->where('stage_name', 'like', $like)
                    ->orWhere('real_name', 'like', $like)
                    ->orWhere('genre', 'like', $like)
                    ->orWhereHas('user', function ($uq) use ($like) {
                        $uq->where('name', 'like', $like)
                           ->orWhere('username', 'like', $like)
                           ->orWhere('email', 'like', $like);
                    });
            });
        }

        // Filter by genre if provided
        if ($request->has('genre') && $request->genre) {
            $genreValue = trim($request->genre);
            
            // Try to find genre by slug (since useNames=true sends slugs)
            $genre = Genre::where('slug', $genreValue)->orWhere('slug', strtolower($genreValue))->first();
            
            if ($genre) {
                // Filter by genre relationship
                $query->whereHas('genres', function ($q) use ($genre) {
                    $q->where('genres.id', $genre->id);
                });
            } else {
                // Fallback: check if it's a numeric ID
                if (is_numeric($genreValue)) {
                    $query->whereHas('genres', function ($q) use ($genreValue) {
                        $q->where('genres.id', $genreValue);
                    });
                } else {
                    // Fallback: check legacy genre field
                    $query->where('genre', $genreValue);
                }
            }
        }

        $artists = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        if ($request->ajax()) {
            return view('admin.artists._results', compact('artists'));
        }

        return view('admin.artists.index', compact('artists'));
    }

    public function create()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'artist');
        })->get();

        return view('admin.artists.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => ['required', 'string', 'max:255', UniqueNormalizedName::forArtist()],
            'real_name' => 'nullable|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
            'user_id' => 'nullable|exists:users,id',
            'is_unclaimed' => 'nullable|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'youtube_videos' => 'nullable|array',
            'youtube_videos.*' => ['nullable', new YoutubeUrl()],
        ]);

        // Custom validation: contact_email is required for unclaimed artists
        $validator->after(function ($validator) use ($request) {
            if ($request->has('is_unclaimed') && $request->is_unclaimed && empty($request->contact_email)) {
                $validator->errors()->add('contact_email', 'Contact email is required for unclaimed artists.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['profile_picture']);
        
        // If marked as unclaimed or no user selected, set user_id to null
        if ($request->has('is_unclaimed') && $request->is_unclaimed) {
            $data['user_id'] = null;
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('artists/profile_pictures', 'public');
        }

        $artist = Artist::create($data);

        // Handle YouTube videos
        if ($request->has('youtube_videos') && is_array($request->youtube_videos)) {
            foreach ($request->youtube_videos as $index => $url) {
                if (!empty($url)) {
                    $videoId = YoutubeVideo::extractVideoId($url);
                    if ($videoId) {
                        YoutubeVideo::create([
                            'videoable_type' => Artist::class,
                            'videoable_id' => $artist->id,
                            'youtube_url' => $url,
                            'youtube_video_id' => $videoId,
                            'order' => $index,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.artists.index')
            ->with('success', 'Artist created successfully.');
    }

    public function show(Artist $artist)
    {
        $artist->load('user');

        return view('admin.artists.show', compact('artist'));
    }

    public function edit(Artist $artist)
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'artist');
        })->get();

        return view('admin.artists.edit', compact('artist', 'users'));
    }

    public function update(Request $request, Artist $artist)
    {
        // Log incoming request for debugging
        \Log::info('Artist update request received', [
            'artist_id' => $artist->id,
            'request_data' => $request->except(['profile_picture', '_token', '_method']),
        ]);

        $validator = Validator::make($request->all(), [
            'stage_name' => ['required', 'string', 'max:255', UniqueNormalizedName::forArtist($artist->id)],
            'real_name' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'required|string',
            'phone_number' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'user_id' => 'nullable|exists:users,id',
            'youtube_videos' => 'nullable|array',
            'youtube_videos.*' => ['nullable', new YoutubeUrl()],
            'youtube_video_ids' => 'nullable|array',
            'youtube_video_ids.*' => 'nullable|integer|exists:youtube_videos,id',
        ]);

        // Custom validation: contact_email is required for unclaimed artists
        $validator->after(function ($validator) use ($request, $artist) {
            $isUnclaimed = empty($request->user_id) || $artist->user_id === null;
            if ($isUnclaimed && empty($request->contact_email)) {
                $validator->errors()->add('contact_email', 'Contact email is required for unclaimed artists.');
            }
        });

        if ($validator->fails()) {
            \Log::warning('Artist update validation failed', [
                'artist_id' => $artist->id,
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['profile_picture', '_token', '_method']),
            ]);
            return back()->withErrors($validator)->withInput($request->except(['profile_picture']));
        }

        // Handle profile picture upload first
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            try {
                $file = $request->file('profile_picture');
                
                // Validate file was uploaded successfully
                if (!$file->isValid()) {
                    return back()->withErrors(['profile_picture' => 'The uploaded file is invalid.'])->withInput($request->except(['profile_picture']));
                }

                // Delete old profile picture if exists
                if ($artist->profile_picture && Storage::disk('public')->exists($artist->profile_picture)) {
                    Storage::disk('public')->delete($artist->profile_picture);
                }

                // Store new profile picture
                $profilePicturePath = $file->store('artists/profile_pictures', 'public');
                
                // Verify the file was stored
                if (!$profilePicturePath || !Storage::disk('public')->exists($profilePicturePath)) {
                    \Log::error('Profile picture storage failed', ['path' => $profilePicturePath ?? 'null']);
                    return back()->withErrors(['profile_picture' => 'Failed to save profile picture. Please try again.'])->withInput($request->except(['profile_picture']));
                }
            } catch (\Exception $e) {
                \Log::error('Profile picture upload error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return back()->withErrors(['profile_picture' => 'Failed to upload profile picture: ' . $e->getMessage()])->withInput($request->except(['profile_picture']));
            }
        }

        // Prepare data array - collect all form fields
        $data = [
            'stage_name' => $request->input('stage_name'),
            'real_name' => $request->input('real_name'),
            'genre' => $request->input('genre'),
            'bio' => $request->input('bio'),
            'phone_number' => $request->input('phone_number'),
            'contact_email' => $request->input('contact_email'),
            'instagram' => $request->input('instagram'),
            'facebook' => $request->input('facebook'),
            'twitter' => $request->input('twitter'),
            'user_id' => $request->input('user_id', null), // Use null as default if empty
        ];

        // Add profile picture if uploaded
        if ($profilePicturePath !== null) {
            $data['profile_picture'] = $profilePicturePath;
        }

        // Remove null values but keep empty strings (for clearing fields)
        $updateData = [];
        foreach ($data as $key => $value) {
            if ($value !== null || $key === 'user_id') {
                // Allow null for user_id (for unclaimed artists)
                $updateData[$key] = $value;
            }
        }

        // Ensure we have data to update
        if (empty($updateData)) {
            \Log::warning('Artist update called with no data', ['artist_id' => $artist->id, 'request_data' => $request->all()]);
            return back()->withErrors(['general' => 'No data provided to update.'])->withInput($request->except(['profile_picture']));
        }

        try {
            $artist->update($updateData);
            \Log::info('Artist updated successfully', [
                'artist_id' => $artist->id, 
                'fields_updated' => array_keys($updateData)
            ]);
        } catch (\Exception $e) {
            \Log::error('Artist update failed', [
                'artist_id' => $artist->id,
                'error' => $e->getMessage(),
                'data_attempted' => $updateData,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['general' => 'Failed to update artist: ' . $e->getMessage()])->withInput($request->except(['profile_picture']));
        }

        // Handle YouTube videos - delete removed videos and add new ones
        if ($request->has('youtube_video_ids')) {
            // Delete videos that are not in the list
            $artist->youtubeVideos()->whereNotIn('id', array_filter($request->youtube_video_ids))->delete();
        } else {
            // If no video IDs provided, delete all existing videos
            $artist->youtubeVideos()->delete();
        }

        // Add new YouTube videos
        if ($request->has('youtube_videos') && is_array($request->youtube_videos)) {
            $existingVideoIds = $request->youtube_video_ids ?? [];
            $orderOffset = $artist->youtubeVideos()->whereIn('id', array_filter($existingVideoIds))->count();
            
            foreach ($request->youtube_videos as $index => $url) {
                if (!empty($url)) {
                    $videoId = YoutubeVideo::extractVideoId($url);
                    if ($videoId) {
                        YoutubeVideo::create([
                            'videoable_type' => Artist::class,
                            'videoable_id' => $artist->id,
                            'youtube_url' => $url,
                            'youtube_video_id' => $videoId,
                            'order' => $orderOffset + $index,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.artists.show', $artist)
            ->with('success', 'Artist updated successfully.');
    }

    public function destroy(Artist $artist)
    {
        $artist->delete();

        return redirect()->route('admin.artists.index')
            ->with('success', 'Artist deleted successfully.');
    }

    public function toggleStatus(Artist $artist)
    {
        $artist->update(['is_active' => ! $artist->is_active]);

        return back()->with('success', 'Artist status updated successfully.');
    }

    /**
     * Export artists to CSV
     */
    public function export(Request $request)
    {
        $query = Artist::with('user');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('stage_name', 'like', $like)
                    ->orWhere('real_name', 'like', $like)
                    ->orWhere('genre', 'like', $like)
                    ->orWhereHas('user', function ($uq) use ($like) {
                        $uq->where('name', 'like', $like)
                           ->orWhere('username', 'like', $like)
                           ->orWhere('email', 'like', $like);
                    });
            });
        }

        // Filter by genre if provided
        if ($request->has('genre') && $request->genre) {
            $genreValue = trim($request->genre);
            
            // Try to find genre by slug (since useNames=true sends slugs)
            $genre = Genre::where('slug', $genreValue)->orWhere('slug', strtolower($genreValue))->first();
            
            if ($genre) {
                // Filter by genre relationship
                $query->whereHas('genres', function ($q) use ($genre) {
                    $q->where('genres.id', $genre->id);
                });
            } else {
                // Fallback: check if it's a numeric ID
                if (is_numeric($genreValue)) {
                    $query->whereHas('genres', function ($q) use ($genreValue) {
                        $q->where('genres.id', $genreValue);
                    });
                } else {
                    // Fallback: check legacy genre field
                    $query->where('genre', $genreValue);
                }
            }
        }

        $artists = $query->orderBy('created_at', 'desc')->get();

        $filename = 'artists_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($artists) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Stage Name',
                'Real Name',
                'Genre',
                'Bio',
                'Phone Number',
                'Contact Email',
                'Instagram',
                'Facebook',
                'Twitter',
                'User ID',
                'User Email',
                'User Name',
                'Created At',
                'Updated At'
            ]);

            // CSV Data
            foreach ($artists as $artist) {
                fputcsv($file, [
                    $artist->id,
                    $artist->stage_name,
                    $artist->real_name ?? '',
                    $artist->genre ?? '',
                    $artist->bio ?? '',
                    $artist->phone_number ?? '',
                    $artist->contact_email ?? '',
                    $artist->instagram ?? '',
                    $artist->facebook ?? '',
                    $artist->twitter ?? '',
                    $artist->user_id ?? '',
                    $artist->user->email ?? '',
                    $artist->user->name ?? '',
                    $artist->created_at->format('Y-m-d H:i:s'),
                    $artist->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download a CSV template for artist import.
     *
     * Uses the same header structure as the export so that
     * uploaded CSV files match the expected column order.
     */
    public function downloadImportTemplate()
    {
        $filename = 'artists_import_template.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // CSV Headers (keep in sync with export())
            fputcsv($file, [
                'ID',
                'Stage Name',
                'Real Name',
                'Genre',
                'Bio',
                'Phone Number',
                'Contact Email',
                'Instagram',
                'Facebook',
                'Twitter',
                'User ID',
                'User Email',
                'User Name',
                'Created At',
                'Updated At',
            ]);

            // Optionally provide an empty example row
            fputcsv($file, [
                '',        // ID (leave blank for new artists)
                '',        // Stage Name (required)
                '',        // Real Name
                '',        // Genre (required)
                '',        // Bio
                '',        // Phone Number
                '',        // Contact Email (auto-generated for unclaimed if blank)
                '',        // Instagram
                '',        // Facebook
                '',        // Twitter
                '',        // User ID (optional existing artist user)
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
     * Import artists from CSV
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
                    'stage_name' => trim($data[1] ?? ''),
                    'real_name' => trim($data[2] ?? ''),
                    'genre' => trim($data[3] ?? ''),
                    'bio' => trim($data[4] ?? ''),
                    'phone_number' => trim($data[5] ?? ''),
                    'contact_email' => trim($data[6] ?? ''),
                    'instagram' => trim($data[7] ?? ''),
                    'facebook' => trim($data[8] ?? ''),
                    'twitter' => trim($data[9] ?? ''),
                    'user_id' => !empty($data[10]) ? (int)$data[10] : null,
                ];

                // Validate required fields
                if (empty($rowData['stage_name'])) {
                    $errors[] = "Row {$rowNum}: Stage name is required";
                    $skipped++;
                    continue;
                }

                if (empty($rowData['genre'])) {
                    $errors[] = "Row {$rowNum}: Genre is required";
                    $skipped++;
                    continue;
                }

                // Check if artist exists (by ID or stage_name)
                $artist = null;
                if (!empty($rowData['id'])) {
                    $artist = Artist::find($rowData['id']);
                }
                
                if (!$artist) {
                    $artist = Artist::where('stage_name', $rowData['stage_name'])->first();
                }

                // Validate user_id if provided
                if (!empty($rowData['user_id'])) {
                    $user = User::find($rowData['user_id']);
                    if (!$user) {
                        $errors[] = "Row {$rowNum}: User ID {$rowData['user_id']} not found";
                        $skipped++;
                        continue;
                    }
                }

                // Generate contact_email if missing and artist is unclaimed
                if (empty($rowData['contact_email']) && empty($rowData['user_id'])) {
                    $base = Str::slug($rowData['stage_name']);
                    $rowData['contact_email'] = $base . '+' . time() . rand(1000, 9999) . '@example.local';
                }

                try {
                    if ($artist) {
                        // Update existing artist
                        $artist->update($rowData);
                    } else {
                        // Create new artist
                        Artist::create($rowData);
                    }
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                    $skipped++;
                }
            }
            
            fclose($handle);
        }

        $message = "Import completed: {$imported} artists imported";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped";
        }
        if (!empty($errors)) {
            $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= " (and " . (count($errors) - 10) . " more)";
            }
        }

        return redirect()->route('admin.artists.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }
}
