<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArtistsExport;
use App\Exports\ArtistsImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ArtistsImport;
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
use Maatwebsite\Excel\Facades\Excel;

class ArtistManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildFilteredArtistQuery($request);
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
            // Superuser creating unclaimed artist: do not use their own email as artist contact
            if ($request->has('is_unclaimed') && $request->is_unclaimed && $request->filled('contact_email') && auth()->check() && auth()->user()->hasRole('superuser')) {
                $adminEmail = auth()->user()->email;
                if (strtolower(trim($request->contact_email)) === strtolower($adminEmail)) {
                    $validator->errors()->add('contact_email', 'Do not use your own email as the unclaimed artist\'s contact. Enter the artist\'s contact email.');
                }
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
            // Superuser editing unclaimed artist: do not use their own email as artist contact
            if ($isUnclaimed && $request->filled('contact_email') && auth()->check() && auth()->user()->hasRole('superuser')) {
                $adminEmail = auth()->user()->email;
                if (strtolower(trim($request->contact_email)) === strtolower($adminEmail)) {
                    $validator->errors()->add('contact_email', 'Do not use your own email as the unclaimed artist\'s contact. Enter the artist\'s contact email.');
                }
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
     * Build artist query with search and genre filters.
     */
    protected function buildFilteredArtistQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Artist::with('user');

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

        if ($request->has('genre') && $request->genre) {
            $genreValue = trim($request->genre);
            $genre = Genre::where('slug', $genreValue)->orWhere('slug', strtolower($genreValue))->first();

            if ($genre) {
                $query->whereHas('genres', function ($q) use ($genre) {
                    $q->where('genres.id', $genre->id);
                });
            } elseif (is_numeric($genreValue)) {
                $query->whereHas('genres', function ($q) use ($genreValue) {
                    $q->where('genres.id', $genreValue);
                });
            } else {
                $query->where('genre', $genreValue);
            }
        }

        return $query;
    }

    /**
     * Export artists to Excel
     */
    public function export(Request $request)
    {
        $query = $this->buildFilteredArtistQuery($request);
        $artists = $query->orderBy('created_at', 'desc')->get();

        $filename = 'artists_export_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ArtistsExport($artists), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Download an Excel template for artist import.
     */
    public function downloadImportTemplate()
    {
        $filename = 'artists_import_template.xlsx';

        return Excel::download(new ArtistsImportTemplateExport, $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Import artists from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excel_file.required' => 'Please select an Excel file to import.',
            'excel_file.mimes' => 'The file must be an Excel file (.xlsx, .xls) or CSV.',
        ]);

        $file = $request->file('excel_file');

        $import = new ArtistsImport;
        Excel::import($import, $file);

        $message = "Import completed: {$import->imported} artists imported";
        if ($import->skipped > 0) {
            $message .= ", {$import->skipped} skipped";
        }
        if (! empty($import->errors)) {
            $message .= '. ' . count($import->errors) . ' error(s) occurred.';
        }

        return redirect()->route('admin.artists.index')
            ->with('success', $message)
            ->with('import_errors', $import->errors);
    }
}
