<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venue;
use App\Models\VenueOwnerRequest;
use App\Models\YoutubeVideo;
use App\Rules\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VenueController extends Controller
{
    /**
     * Search venues for the venue selector component
     */
    public function search(Request $request)
    {
        $query = Venue::with(['owner']);

        // Handle search term
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('address', 'like', "%{$searchTerm}%")
                    ->orWhere('city', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle location filter
        if ($request->filled('location')) {
            $query->where('city', 'like', "%{$request->location}%");
        }

        // Handle capacity filters
        if ($request->filled('capacity_min')) {
            $query->where('capacity', '>=', $request->capacity_min);
        }
        if ($request->filled('capacity_max')) {
            $query->where('capacity', '<=', $request->capacity_max);
        }

        // Get user role and ID for smart ordering
        $userRole = $request->get('user_role', 'all');
        $organiserId = $request->get('organiser_id');
        $artistId = $request->get('artist_id');

        // Smart ordering based on user role
        if ($userRole === 'organiser' && $organiserId) {
            // Show own venues first, then others
            $query->orderByRaw("CASE WHEN owner_id = ? AND owner_type = 'organiser' THEN 0 ELSE 1 END", [$organiserId]);
        } elseif ($userRole === 'artist' && $artistId) {
            // Show venues where artist has performed, then others
            $query->orderByRaw("CASE WHEN id IN (SELECT DISTINCT venue_id FROM event_artists ea JOIN events e ON ea.event_id = e.id WHERE ea.artist_id = ?) THEN 0 ELSE 1 END", [$artistId]);
        }

        // Default ordering
        $query->orderBy('name');

        // Pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $total = $query->count();
        $venues = $query->offset($offset)->limit($limit)->get();

        // Add ownership information
        $venues = $venues->map(function ($venue) use ($userRole, $organiserId, $artistId) {
            $venue->isOwnVenue = false;
            
            if ($userRole === 'organiser' && $organiserId && $venue->owner_id == $organiserId && $venue->owner_type === 'organiser') {
                $venue->isOwnVenue = true;
            } elseif ($userRole === 'artist' && $artistId && $venue->owner_id == $artistId && $venue->owner_type === 'artist') {
                $venue->isOwnVenue = true;
            }

            return $venue;
        });

        return response()->json([
            'venues' => $venues,
            'pagination' => [
                'page' => (int) $page,
                'limit' => (int) $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venue::with(['owner', 'events']);

        // If user is logged in, determine their owned venue IDs for prioritisation
        $ownedVenueIds = [];
        if (Auth::check()) {
            $user = Auth::user();
            try {
                $ownedVenueIds = Venue::where('user_id', $user->id)
                    ->orWhereHas('owners', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->pluck('id')
                    ->toArray();
            } catch (\Exception $e) {
                // Fallback if venue_owners table doesn't exist
                $ownedVenueIds = Venue::where('user_id', $user->id)->pluck('id')->toArray();
            }
        }

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('address', 'like', "%{$searchTerm}%")
                    ->orWhere('city', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle capacity filter
        if ($request->filled('capacity_filter')) {
            switch ($request->capacity_filter) {
                case 'large':
                    $query->where('capacity', '>=', 500);
                    break;
                case 'medium':
                    $query->whereBetween('capacity', [100, 499]);
                    break;
                case 'small':
                    $query->where('capacity', '<', 100);
                    break;
            }
        }

        // Handle sort
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'capacity':
                $query->orderBy('capacity', 'desc');
                break;
            case 'events':
                $query->withCount('events')->orderBy('events_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Prioritise owned venues (top) while keeping other sort order
        if (!empty($ownedVenueIds)) {
            // Ensure owned venues appear first; duplicates are fine as DB will handle orderBy chain
            $idsList = implode(',', $ownedVenueIds);
            $query->orderByRaw("FIELD(id, {$idsList}) DESC");
        }

        $perPage = $request->get('per_page', 12);
        $venues = $query->paginate($perPage);

        // Handle AJAX requests - return only the results content
        if ($request->ajax()) {
            return view('venues._list', compact('venues', 'ownedVenueIds'));
        }

        return view('venues.index', compact('venues', 'ownedVenueIds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('venues.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // #region agent log
        @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'VenueController.php:store:entry','message'=>'Frontend venue CREATE entry','data'=>['request_contact_email'=>$request->input('contact_email'),'request_name'=>$request->input('name')],'timestamp'=>now()->timestamp*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F'])."\n", FILE_APPEND | LOCK_EX);
        // #endregion

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10', // Max 10 images
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
        ]);

        // Ensure contact_email is not null for DB constraint
        if (empty($validated['contact_email'])) {
            $base = Str::slug($validated['name'] ?? 'venue');
            $validated['contact_email'] = $base.'+'.(Auth::id() ?? 'guest').'-'.time().'@example.local';
        }

        // Set owner and user based on authentication status
        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
            $validated['owner_id'] = Auth::id();
            $validated['owner_type'] = Auth::user()->hasRole('artist') ? 'artist' : 'organiser';
            
            // Get user's folder path for authenticated users
            $userFolder = Auth::user()->getFolderPath();
            $venueFolder = $this->createVenueFolder($userFolder, $validated['name']);
        } else {
            // For unauthenticated users, set default values
            $validated['user_id'] = null;
            $validated['owner_id'] = null;
            $validated['owner_type'] = 'guest';
            
            // Create a default folder structure for guest users
            $venueFolder = 'public/guest_venues/' . Str::slug($validated['name']) . '_' . time();
            Storage::disk('public')->makeDirectory($venueFolder.'/images');
            Storage::disk('public')->makeDirectory($venueFolder.'/gallery');
            Storage::disk('public')->makeDirectory($venueFolder.'/documents');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['main_picture'] = $request->file('image')->store($venueFolder.'/images', 'public');
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($venueFolder.'/gallery', 'public');
            }
            $validated['venue_gallery'] = json_encode($galleryPaths);
        }

        // Convert amenities array to JSON
        if (isset($validated['amenities'])) {
            $validated['amenities'] = json_encode($validated['amenities']);
        }

        $venue = Venue::create($validated);

        // Handle YouTube videos
        if ($request->has('youtube_videos') && is_array($request->youtube_videos)) {
            foreach ($request->youtube_videos as $index => $url) {
                if (!empty($url)) {
                    $videoId = YoutubeVideo::extractVideoId($url);
                    if ($videoId) {
                        YoutubeVideo::create([
                            'videoable_type' => Venue::class,
                            'videoable_id' => $venue->id,
                            'youtube_url' => $url,
                            'youtube_video_id' => $videoId,
                            'order' => $index,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('venues.show', $venue)
            ->with('success', 'Venue created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Venue $venue)
    {
        // #region agent log
        try {
            $logData = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'A',
                'location' => 'VenueController.php:242',
                'message' => 'Venue show method entry',
                'data' => [
                    'venue_id' => $venue->id,
                    'venue_name' => $venue->name,
                    'main_picture' => $venue->main_picture,
                    'user_agent' => $request->userAgent(),
                    'is_facebook_crawler' => $this->isSocialPreviewRequest($request),
                ],
                'timestamp' => now()->timestamp * 1000,
            ];
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // Silently fail logging
        }
        // #endregion

        $venue->load(['owner', 'events' => function ($query) {
            $query->orderBy('date', 'asc');
        }, 'youtubeVideos']);
        
        // Load venue owners and pending requests if user is an owner
        $venueOwners = collect();
        $pendingRequests = collect();
        $isOwner = false;
        $isPrimaryOwner = false;
        
        if (auth()->check()) {
            $userId = auth()->id();
            
            // Check ownership - try new system first, then legacy
            try {
                $isOwner = $venue->isOwnedBy($userId);
            } catch (\Exception $e) {
                // If venue_owners table doesn't exist, check legacy
                $isOwner = false;
            }
            
            // Also check direct user_id match (most reliable for legacy venues)
            if (!$isOwner && $venue->user_id === $userId) {
                $isOwner = true;
            }
            
            if ($isOwner) {
                // Try to load from new venue_owners system
                try {
                    // Load owners using relationship
                    $venueOwners = $venue->owners()->get();
                    
                    // If empty, try direct database query as fallback
                    if ($venueOwners->isEmpty()) {
                        $ownerRows = DB::table('venue_owners')
                            ->join('users', 'venue_owners.user_id', '=', 'users.id')
                            ->where('venue_owners.venue_id', $venue->id)
                            ->select('users.*', 'venue_owners.role', 'venue_owners.added_by_user_id', 'venue_owners.added_at')
                            ->get();
                        
                        if ($ownerRows->isNotEmpty()) {
                            // Convert to User models with pivot data
                            $venueOwners = collect();
                            foreach ($ownerRows as $row) {
                                $user = User::find($row->id);
                                if ($user) {
                                    // Manually set pivot data as object property
                                    $pivot = new \stdClass();
                                    $pivot->role = $row->role;
                                    $pivot->added_by_user_id = $row->added_by_user_id;
                                    $pivot->added_at = $row->added_at;
                                    $user->pivot = $pivot;
                                    $venueOwners->push($user);
                                }
                            }
                        }
                    }
                    
                    // If still empty but user is owner via legacy, ensure entry exists
                    if ($venueOwners->isEmpty() && $venue->user_id === $userId) {
                        // Check if entry already exists in database
                        $exists = DB::table('venue_owners')
                            ->where('venue_id', $venue->id)
                            ->where('user_id', $userId)
                            ->exists();
                        
                        if (!$exists) {
                            try {
                                $venue->owners()->attach($userId, [
                                    'role' => 'primary',
                                    'added_by_user_id' => $userId,
                                    'added_at' => $venue->created_at ?? now(),
                                ]);
                            } catch (\Exception $e) {
                                // Entry might have been created, continue
                            }
                        }
                        // Reload owners after potential attach
                        $venueOwners = $venue->owners()->get();
                    }
                    
                    // Determine primary owner - SIMPLIFIED AND RELIABLE
                    $isPrimaryOwner = false;
                    
                    // Method 1: Direct database query (most reliable)
                    $primaryOwnerCheck = DB::table('venue_owners')
                        ->where('venue_id', $venue->id)
                        ->where('user_id', $userId)
                        ->where('role', 'primary')
                        ->exists();
                    
                    if ($primaryOwnerCheck) {
                        $isPrimaryOwner = true;
                    } else {
                        // Method 2: Check legacy user_id
                        if ($venue->user_id === $userId) {
                            $isPrimaryOwner = true;
                        } else {
                            // Method 3: Check loaded collection
                            foreach ($venueOwners as $owner) {
                                if ($owner->id === $userId) {
                                    $role = null;
                                    if (isset($owner->pivot)) {
                                        if (is_object($owner->pivot)) {
                                            $role = $owner->pivot->role ?? null;
                                        } elseif (is_array($owner->pivot)) {
                                            $role = $owner->pivot['role'] ?? null;
                                        }
                                    }
                                    if ($role === 'primary') {
                                        $isPrimaryOwner = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Load pending requests - ALWAYS use direct query for maximum reliability
                    $requestRows = DB::table('venue_owner_requests')
                        ->join('users', 'venue_owner_requests.requester_user_id', '=', 'users.id')
                        ->where('venue_owner_requests.venue_id', $venue->id)
                        ->where('venue_owner_requests.status', 'pending')
                        ->select('venue_owner_requests.*', 'users.name as requester_name', 'users.email as requester_email')
                        ->orderBy('venue_owner_requests.requested_at', 'desc')
                        ->get();
                    
                    $pendingRequests = collect();
                    foreach ($requestRows as $row) {
                        $venueRequest = VenueOwnerRequest::find($row->id);
                        if ($venueRequest) {
                            $user = User::find($row->requester_user_id);
                            if ($user) {
                                $venueRequest->setRelation('requester', $user);
                            }
                            $pendingRequests->push($venueRequest);
                        }
                    }
                } catch (\Exception $e) {
                    // New system not available, use legacy
                    $venueOwners = collect();
                    $pendingRequests = collect();
                    // If owner via legacy system (user_id match), assume primary
                    $isPrimaryOwner = ($venue->user_id === $userId);
                    
                    // Still try to load pending requests even if owners failed
                    try {
                        $requestRows = DB::table('venue_owner_requests')
                            ->join('users', 'venue_owner_requests.requester_user_id', '=', 'users.id')
                            ->where('venue_owner_requests.venue_id', $venue->id)
                            ->where('venue_owner_requests.status', 'pending')
                            ->select('venue_owner_requests.*', 'users.name as requester_name', 'users.email as requester_email')
                            ->orderBy('venue_owner_requests.requested_at', 'desc')
                            ->get();
                        
                        $pendingRequests = collect();
                        foreach ($requestRows as $row) {
                            $venueRequest = VenueOwnerRequest::find($row->id);
                            if ($venueRequest) {
                                $user = User::find($row->requester_user_id);
                                if ($user) {
                                    $venueRequest->setRelation('requester', $user);
                                }
                                $pendingRequests->push($venueRequest);
                            }
                        }
                    } catch (\Exception $e2) {
                        // Ignore if requests table doesn't exist
                    }
                }
            }
        }

        // Handle social media crawlers (Facebook, Twitter, etc.)
        if ($this->isSocialPreviewRequest($request)) {
            $shareData = $this->buildSocialPreviewData($venue);

            // #region agent log
            $logData = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'C',
                'location' => 'VenueController.php:270',
                'message' => 'Social preview request detected, returning share preview',
                'data' => [
                    'share_data' => $shareData,
                ],
                'timestamp' => now()->timestamp * 1000,
            ];
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion

            return response()
                ->view('venues.share-preview', compact('venue', 'shareData'))
                ->header('Cache-Control', 'public, max-age=600')
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        // Normalize gallery to array for the view
        $gallery = [];
        if (is_array($venue->venue_gallery)) {
            $gallery = $venue->venue_gallery;
        } elseif (is_string($venue->venue_gallery) && ! empty($venue->venue_gallery)) {
            $parsed = json_decode($venue->venue_gallery, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $gallery = $parsed;
            } else {
                $gallery = array_filter(array_map('trim', explode(',', $venue->venue_gallery)));
            }
        }

        // #region agent log
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B',
            'location' => 'VenueController.php:295',
            'message' => 'Image path checks before URL generation',
            'data' => [
                'main_picture_exists' => $venue->main_picture ? Storage::disk('public')->exists($venue->main_picture) : false,
                'main_picture_path' => $venue->main_picture,
                'gallery_count' => count($gallery),
                'first_gallery_exists' => count($gallery) > 0 ? Storage::disk('public')->exists($gallery[0]) : false,
            ],
            'timestamp' => now()->timestamp * 1000,
        ];
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion

        $upcomingEvents = $venue->events
            ->filter(function ($e) {
                return $e->date && $e->date >= now();
            })
            ->values();

        $ratingAvg = round((float) ($venue->ratings()->avg('rating') ?? 0), 1);

        return view('venues.show', compact(
            'venue',
            'gallery',
            'upcomingEvents',
            'ratingAvg',
            'venueOwners',
            'pendingRequests',
            'isOwner',
            'isPrimaryOwner'
        ));
    }

    private function isSocialPreviewRequest(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        $previewAgents = [
            'facebookexternalhit',
            'facebot',
            'twitterbot',
            'pinterest',
            'linkedinbot',
            'slackbot',
            'discordbot',
            'whatsapp',
            'telegrambot',
            'vkshare',
            'skypeuripreview',
        ];

        foreach ($previewAgents as $agent) {
            if ($userAgent && str_contains($userAgent, $agent)) {
                return true;
            }
        }

        return $request->boolean('share_preview');
    }

    private function buildSocialPreviewData(Venue $venue): array
    {
        $description = Str::limit(strip_tags($venue->description ?? 'Discover this amazing venue on My Gig Guide.'), 160);

        $imageUrl = null;

        // #region agent log
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'VenueController.php:buildSocialPreviewData',
            'message' => 'Building social preview data - checking images',
            'data' => [
                'venue_id' => $venue->id,
                'main_picture' => $venue->main_picture,
                'main_picture_exists' => $venue->main_picture ? Storage::disk('public')->exists($venue->main_picture) : false,
            ],
            'timestamp' => now()->timestamp * 1000,
        ];
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion

        // Try main picture first - match EventController pattern exactly
        if ($venue->main_picture && Storage::disk('public')->exists($venue->main_picture)) {
            $storageUrl = Storage::disk('public')->url($venue->main_picture);
            // Ensure absolute URL - Storage::url() may return relative if APP_URL not set
            $imageUrl = (str_starts_with($storageUrl, 'http://') || str_starts_with($storageUrl, 'https://')) 
                ? $storageUrl 
                : url($storageUrl);
            
            // #region agent log
            $logData = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'A',
                'location' => 'VenueController.php:buildSocialPreviewData',
                'message' => 'Main picture URL generated for social preview',
                'data' => [
                    'final_url' => $imageUrl,
                    'is_absolute' => str_starts_with($imageUrl, 'http'),
                ],
                'timestamp' => now()->timestamp * 1000,
            ];
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
        } else {
            // Try gallery - normalize same way as show() method
            $gallery = [];
            if (is_array($venue->venue_gallery)) {
                $gallery = $venue->venue_gallery;
            } elseif (is_string($venue->venue_gallery) && ! empty($venue->venue_gallery)) {
                $parsed = json_decode($venue->venue_gallery, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    $gallery = $parsed;
                } else {
                    $gallery = array_filter(array_map('trim', explode(',', $venue->venue_gallery)));
                }
            }

            if (count($gallery) > 0 && Storage::disk('public')->exists($gallery[0])) {
                $storageUrl = Storage::disk('public')->url($gallery[0]);
                // Ensure absolute URL - Storage::url() may return relative if APP_URL not set
                $imageUrl = (str_starts_with($storageUrl, 'http://') || str_starts_with($storageUrl, 'https://')) 
                    ? $storageUrl 
                    : url($storageUrl);
                
                // #region agent log
                $logData = [
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'A',
                    'location' => 'VenueController.php:buildSocialPreviewData',
                    'message' => 'Gallery image URL generated for social preview',
                    'data' => [
                        'final_url' => $imageUrl,
                        'is_absolute' => str_starts_with($imageUrl, 'http'),
                    ],
                    'timestamp' => now()->timestamp * 1000,
                ];
                @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
                // #endregion
            }
        }

        // Fallback to logo
        if (!$imageUrl) {
            $imageUrl = url(asset('logos/logo1.jpeg'));
            
            // #region agent log
            $logData = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'VenueController.php:buildSocialPreviewData',
                'message' => 'Using fallback logo for social preview',
                'data' => [
                    'fallback_url' => $fallbackUrl,
                    'final_url' => $imageUrl,
                ],
                'timestamp' => now()->timestamp * 1000,
            ];
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
        }

        // #region agent log
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'E',
            'location' => 'VenueController.php:buildSocialPreviewData',
            'message' => 'Final social preview data',
            'data' => [
                'final_image_url' => $imageUrl,
                'is_absolute' => str_starts_with($imageUrl, 'http'),
            ],
            'timestamp' => now()->timestamp * 1000,
        ];
        file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion

        return [
            'title' => $venue->name.' - My Gig Guide',
            'description' => $description,
            'image' => $imageUrl,
            'url' => route('venues.show', $venue),
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venue $venue)
    {
        // Load venue owners and pending requests if user is an owner
        $venueOwners = collect();
        $pendingRequests = collect();
        $isOwner = false;
        $isPrimaryOwner = false;
        
        if (auth()->check()) {
            try {
                $isOwner = $venue->isOwnedBy(auth()->id());
                if ($isOwner) {
                    $venueOwners = $venue->owners()->get();
                    $primaryOwner = $venue->owners()->wherePivot('role', 'primary')->first();
                    $isPrimaryOwner = $primaryOwner && $primaryOwner->id === auth()->id();
                    $pendingRequests = $venue->venueOwnerRequests()
                        ->where('status', 'pending')
                        ->with(['requester'])
                        ->get();
                }
            } catch (\Exception $e) {
                // If venue_owners table doesn't exist, fallback to legacy check
                $isOwner = ($venue->user_id === auth()->id()) || 
                          ($venue->owner_id && $venue->owner_type === \App\Models\User::class && $venue->owner_id === auth()->id());
            }
        }
        // Authorize: admin/superuser OR creator OR linked owner user
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'superuser']);

        $ownsViaCreator = $venue->user_id === Auth::id();

        $ownsViaOwner = false;
        if ($venue->owner_id && $venue->owner_type) {
            // If the owner is a User record
            if ($venue->owner_type === \App\Models\User::class) {
                $ownsViaOwner = $venue->owner_id === Auth::id();
            } else {
                // Owner might be Artist or Organiser models that have a user_id
                $ownerModel = $venue->owner; // polymorphic relation
                if ($ownerModel && isset($ownerModel->user_id)) {
                    $ownsViaOwner = (int) $ownerModel->user_id === (int) Auth::id();
                }
            }
        }

        // Also check new venue_owners system for authorization
        if (! $isAdmin && ! $ownsViaCreator && ! $ownsViaOwner && ! $isOwner) {
            abort(403, 'Unauthorized');
        }

        return view('venues.edit', compact('venue', 'venueOwners', 'pendingRequests', 'isOwner', 'isPrimaryOwner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venue $venue)
    {
        // Authorize: admin/superuser OR creator OR linked owner user
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'superuser']);
        $isOwner = false;
        if ($user) {
            try {
                // Use unified ownership check so co-owners/managers can also edit
                $isOwner = $venue->isOwnedBy($user->id);
            } catch (\Exception $e) {
                // If venue_owners table doesn't exist, fall back to legacy checks below
                $isOwner = false;
            }
        }
        $ownsViaCreator = $venue->user_id === Auth::id();
        $ownsViaOwner = false;
        if ($venue->owner_id && $venue->owner_type) {
            if ($venue->owner_type === \App\Models\User::class) {
                $ownsViaOwner = $venue->owner_id === Auth::id();
            } else {
                $ownerModel = $venue->owner;
                if ($ownerModel && isset($ownerModel->user_id)) {
                    $ownsViaOwner = (int) $ownerModel->user_id === (int) Auth::id();
                }
            }
        }
        if (! $isAdmin && ! $ownsViaCreator && ! $ownsViaOwner && ! $isOwner) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10', // Max 10 images
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
            'youtube_videos' => 'nullable|array',
            'youtube_videos.*' => ['nullable', new YoutubeUrl()],
            'youtube_video_ids' => 'nullable|array',
            'youtube_video_ids.*' => 'nullable|integer|exists:youtube_videos,id',
        ]);

        // Ensure contact_email is not null for DB constraint
        if (empty($validated['contact_email'])) {
            $base = Str::slug($validated['name'] ?? 'venue');
            $validated['contact_email'] = $base.'+'.(Auth::id() ?? 'sys').'-'.time().'@example.local';
        }

        // Get user's folder path
        $userFolder = Auth::user()->getFolderPath();

        // Create venue-specific folder
        $venueFolder = $this->createVenueFolder($userFolder, $validated['name']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($venue->main_picture) {
                Storage::disk('public')->delete($venue->main_picture);
            }

            $validated['main_picture'] = $request->file('image')->store($venueFolder.'/images', 'public');
        }

        // Handle gallery uploads
        if ($request->hasFile('gallery')) {
            // Delete old gallery images if they exist
            if ($venue->venue_gallery) {
                foreach ($venue->venue_gallery as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($venueFolder.'/gallery', 'public');
            }
            $validated['venue_gallery'] = json_encode($galleryPaths);
        }

        // Convert amenities array to JSON
        if (isset($validated['amenities'])) {
            $validated['amenities'] = json_encode($validated['amenities']);
        }

        $venue->update($validated);

        // Handle YouTube videos - delete removed videos and add new ones
        if ($request->has('youtube_video_ids')) {
            // Delete videos that are not in the list
            $venue->youtubeVideos()->whereNotIn('id', array_filter($request->youtube_video_ids))->delete();
        } else {
            // If no video IDs provided, delete all existing videos
            $venue->youtubeVideos()->delete();
        }

        // Add new YouTube videos
        if ($request->has('youtube_videos') && is_array($request->youtube_videos)) {
            $existingVideoIds = $request->youtube_video_ids ?? [];
            $orderOffset = $venue->youtubeVideos()->whereIn('id', array_filter($existingVideoIds))->count();
            
            foreach ($request->youtube_videos as $index => $url) {
                if (!empty($url)) {
                    $videoId = YoutubeVideo::extractVideoId($url);
                    if ($videoId) {
                        YoutubeVideo::create([
                            'videoable_type' => Venue::class,
                            'videoable_id' => $venue->id,
                            'youtube_url' => $url,
                            'youtube_video_id' => $videoId,
                            'order' => $orderOffset + $index,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('venues.show', $venue)
            ->with('success', 'Venue updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venue $venue)
    {
        // Check if user owns this venue or is admin/superuser
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'superuser']);
        if (! $isAdmin && $venue->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Delete image if exists
        if ($venue->main_picture) {
            Storage::disk('public')->delete($venue->main_picture);
        }

        $venue->delete();

        return redirect()->route('venues.index')
            ->with('success', 'Venue deleted successfully!');
    }

    /**
     * Quick create a venue with minimal information
     */
    public function quickStore(Request $request)
    {
        try {
            // Normalize optional fields so empty strings don't fail validation
            $payload = $request->all();
            if (isset($payload['capacity']) && $payload['capacity'] === '') {
                unset($payload['capacity']);
            }
            if (isset($payload['phone']) && $payload['phone'] === '') {
                unset($payload['phone']);
            }

            $validated = validator($payload, [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'capacity' => 'sometimes|integer|min:1',
                'phone' => 'sometimes|string|max:20',
            ])->validate();

            // Ensure contact_email is not null for DB constraint
            $base = Str::slug($validated['name'] ?? 'venue');
            $validated['contact_email'] = $base.'+'.(Auth::id() ?? 'guest').'-'.time().'@example.local';

            // Set owner and user based on authentication status
            if (Auth::check()) {
                $validated['user_id'] = Auth::id();
                $validated['owner_id'] = Auth::id();
                $validated['owner_type'] = Auth::user()->hasRole('artist') ? 'artist' : 'organiser';
            } else {
                $validated['user_id'] = null;
                $validated['owner_id'] = null;
                $validated['owner_type'] = 'guest';
            }

            // Map phone -> phone_number DB column if provided
            if (isset($validated['phone'])) {
                $validated['phone_number'] = $validated['phone'];
                unset($validated['phone']);
            }

            $venue = Venue::create($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'address' => $venue->address,
                    'capacity' => $venue->capacity,
                    'latitude' => $venue->latitude,
                    'longitude' => $venue->longitude,
                ]);
            }

            return back()->with('success', 'Venue created');
        } catch (ValidationException $e) {
            Log::warning('Quick venue validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Quick venue create failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Server error creating venue',
                ], 500);
            }
            return back()->with('error', 'Server error creating venue');
        }
    }

    /**
     * Create a venue-specific folder structure.
     */
    private function createVenueFolder($userFolder, $venueName)
    {
        // Create a safe folder name
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $venueName));
        $randomSuffix = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $date = now()->format('Y-m-d');

        $folderName = "venue_{$randomSuffix}_{$safeName}_{$date}";
        $venueFolder = $userFolder.'/venues/'.$folderName;

        // Create the folder structure
        Storage::disk('public')->makeDirectory($venueFolder.'/images');
        Storage::disk('public')->makeDirectory($venueFolder.'/gallery');
        Storage::disk('public')->makeDirectory($venueFolder.'/documents');

        return $venueFolder;
    }

    /**
     * Show user's venue ownership requests.
     */
    public function myVenueRequests()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            // Get user's pending requests
            $pendingRequests = VenueOwnerRequest::where('requester_user_id', $user->id)
                ->where('status', 'pending')
                ->with(['venue', 'venue.user'])
                ->latest('requested_at')
                ->get();

            // Get user's approved/rejected requests (for history)
            $pastRequests = VenueOwnerRequest::where('requester_user_id', $user->id)
                ->whereIn('status', ['approved', 'rejected'])
                ->with(['venue', 'venue.user', 'reviewer'])
                ->latest('reviewed_at')
                ->take(20)
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if the error is about missing table
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table or view not found')) {
                // Check if user is admin/superuser - they can run migrations
                if ($user && ($user->hasRole('admin') || $user->hasRole('superuser'))) {
                    return redirect()->route('dashboard')
                        ->withErrors([
                            'migration' => 'The venue_owner_requests table does not exist. Please run: php artisan migrate. Or visit /run-migrations if available.'
                        ]);
                }
                return redirect()->route('dashboard')
                    ->withErrors([
                        'migration' => 'The venue ownership feature is not yet available. Please contact the administrator to run database migrations.'
                    ]);
            }
            throw $e;
        }

        return view('venues.my-requests', compact('pendingRequests', 'pastRequests'));
    }

    /**
     * Submit a request to join a venue.
     */
    public function requestOwnership(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return back()->withErrors(['error' => 'You must be logged in to request venue ownership.']);
        }

        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'reason' => 'required|string|max:1000',
            'proof_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $venue = Venue::findOrFail($validated['venue_id']);

        // Check if user is already an owner (using new many-to-many relationship)
        // Fallback to legacy check if new table doesn't exist yet
        $isOwner = false;
        try {
            $isOwner = $venue->isOwnedBy($user->id);
        } catch (\Illuminate\Database\QueryException $e) {
            // If venue_owners table doesn't exist, check legacy ownership
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table or view not found')) {
                $isOwner = ($venue->user_id === $user->id) || 
                          ($venue->owner_id && $venue->owner_type === \App\Models\User::class && $venue->owner_id === $user->id);
            } else {
                throw $e;
            }
        }
        
        if ($isOwner) {
            $errorMessage = 'You are already an owner of this venue.';
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }
            return back()->withErrors(['venue_id' => $errorMessage]);
        }

        // Check if user already has a pending request for this venue
        // Handle case where table doesn't exist yet
        $existingRequest = null;
        try {
            $existingRequest = VenueOwnerRequest::where('venue_id', $venue->id)
                ->where('requester_user_id', $user->id)
                ->where('status', 'pending')
                ->first();
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Base table or view not found')) {
                // Table doesn't exist - redirect to setup page
                $errorMessage = 'The venue ownership system is not yet set up. Please contact an administrator or visit /setup-venue-tables.php to initialize the system.';
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                return redirect()->back()->withErrors(['venue_id' => $errorMessage]);
            }
            throw $e;
        }

        if ($existingRequest) {
            $errorMessage = 'You already have a pending request for this venue. Please wait for the owner to respond.';
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }
            return back()->withErrors(['venue_id' => $errorMessage]);
        }

        // Handle proof document upload if provided
        $proofPath = null;
        if ($request->hasFile('proof_document')) {
            $proofPath = $request->file('proof_document')->store(
                'venue_requests/proofs',
                'public'
            );
        }

        // Create the request - catch duplicate entry errors
        try {
            $venueRequest = VenueOwnerRequest::create([
                'venue_id' => $venue->id,
                'requester_user_id' => $user->id,
                'status' => 'pending',
                'reason' => $validated['reason'],
                'proof_document_path' => $proofPath,
                'requested_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate entry error
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'unique_venue_requester')) {
                $errorMessage = 'You already have a pending request for this venue. Please wait for the owner to respond.';
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                return back()->withErrors(['venue_id' => $errorMessage]);
            }
            throw $e;
        }

        // Notify venue owners (especially primary owner)
        $this->notifyVenueOwnersOfRequest($venue, $venueRequest, $user);

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Your request to join '{$venue->name}' has been submitted. The venue owner will be notified."
            ], 200);
        }

        return redirect()->back()->with('success', 
            "Your request to join '{$venue->name}' has been submitted. The venue owner will be notified."
        );
    }

    /**
     * Approve a venue ownership request (primary owner only).
     */
    public function approveRequest(Request $request, Venue $venue, VenueOwnerRequest $venueOwnerRequest)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is primary owner
        try {
            $primaryOwner = $venue->owners()->wherePivot('role', 'primary')->first();
            $isPrimaryOwner = $primaryOwner && $primaryOwner->id === $user->id;
        } catch (\Exception $e) {
            // Fallback to legacy check
            $isPrimaryOwner = ($venue->user_id === $user->id) || 
                            ($venue->owner_id && $venue->owner_type === User::class && $venue->owner_id === $user->id);
        }

        if (!$isPrimaryOwner && !$user->hasRole(['admin', 'superuser'])) {
            abort(403, 'Only the primary owner or admin can approve requests.');
        }

        // Verify the request belongs to this venue
        if ($venueOwnerRequest->venue_id !== $venue->id) {
            abort(404, 'Request not found for this venue.');
        }

        if ($venueOwnerRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        // Add the requester as a co-owner
        try {
            $venue->owners()->attach($venueOwnerRequest->requester_user_id, [
                'role' => 'co_owner',
                'added_by_user_id' => $user->id,
                'added_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // User might already be an owner
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->withErrors(['error' => 'This user is already an owner of this venue.']);
            }
            throw $e;
        }

        // Update the request status
        $venueOwnerRequest->update([
            'status' => 'approved',
            'reviewed_by_user_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Ownership request approved. The user has been added as a co-owner.');
    }

    /**
     * Reject a venue ownership request (primary owner only).
     */
    public function rejectRequest(Request $request, Venue $venue, VenueOwnerRequest $venueOwnerRequest)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is primary owner
        try {
            $primaryOwner = $venue->owners()->wherePivot('role', 'primary')->first();
            $isPrimaryOwner = $primaryOwner && $primaryOwner->id === $user->id;
        } catch (\Exception $e) {
            // Fallback to legacy check
            $isPrimaryOwner = ($venue->user_id === $user->id) || 
                            ($venue->owner_id && $venue->owner_type === User::class && $venue->owner_id === $user->id);
        }

        if (!$isPrimaryOwner && !$user->hasRole(['admin', 'superuser'])) {
            abort(403, 'Only the primary owner or admin can reject requests.');
        }

        // Verify the request belongs to this venue
        if ($venueOwnerRequest->venue_id !== $venue->id) {
            abort(404, 'Request not found for this venue.');
        }

        if ($venueOwnerRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        // Update the request status
        $venueOwnerRequest->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => $user->id,
            'reviewed_at' => now(),
            'rejection_reason' => $request->input('rejection_reason', 'Request rejected by venue owner.'),
        ]);

        return back()->with('success', 'Ownership request rejected.');
    }

    /**
     * Remove an owner from a venue (primary owner only).
     */
    public function removeOwner(Request $request, Venue $venue)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if user is primary owner
        try {
            $primaryOwner = $venue->owners()->wherePivot('role', 'primary')->first();
            $isPrimaryOwner = $primaryOwner && $primaryOwner->id === $user->id;
        } catch (\Exception $e) {
            // Fallback to legacy check
            $isPrimaryOwner = ($venue->user_id === $user->id) || 
                            ($venue->owner_id && $venue->owner_type === User::class && $venue->owner_id === $user->id);
        }

        if (!$isPrimaryOwner && !$user->hasRole(['admin', 'superuser'])) {
            abort(403, 'Only the primary owner or admin can remove owners.');
        }

        $userIdToRemove = $request->input('user_id');

        // Prevent removing yourself
        if ($userIdToRemove === $user->id) {
            return back()->withErrors(['error' => 'You cannot remove yourself as an owner.']);
        }

        // Prevent removing primary owner
        try {
            $ownerToRemove = $venue->owners()->where('user_id', $userIdToRemove)->first();
            if ($ownerToRemove && $ownerToRemove->pivot->role === 'primary') {
                return back()->withErrors(['error' => 'Cannot remove the primary owner.']);
            }
        } catch (\Exception $e) {
            // Continue if check fails
        }

        // Remove the owner
        try {
            $venue->owners()->detach($userIdToRemove);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to remove owner: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Owner removed successfully.');
    }

    /**
     * Add a new owner to a venue (primary owner only).
     */
    public function addOwner(Request $request, Venue $venue)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'user_email' => 'required|email|exists:users,email',
            'role' => 'required|in:co_owner,manager',
        ]);

        // Check if user is primary owner
        try {
            $primaryOwner = $venue->owners()->wherePivot('role', 'primary')->first();
            $isPrimaryOwner = $primaryOwner && $primaryOwner->id === $user->id;
        } catch (\Exception $e) {
            // Fallback to legacy check
            $isPrimaryOwner = ($venue->user_id === $user->id) || 
                            ($venue->owner_id && $venue->owner_type === User::class && $venue->owner_id === $user->id);
        }

        if (!$isPrimaryOwner && !$user->hasRole(['admin', 'superuser'])) {
            abort(403, 'Only the primary owner or admin can add owners.');
        }

        // Find user by email
        $userToAdd = User::where('email', $request->input('user_email'))->firstOrFail();

        // Check if user is already an owner
        try {
            if ($venue->isOwnedBy($userToAdd->id)) {
                return back()->withErrors(['user_email' => 'This user is already an owner of this venue.']);
            }
        } catch (\Exception $e) {
            // Continue if check fails
        }

        // Add the user as an owner
        try {
            $venue->owners()->attach($userToAdd->id, [
                'role' => $request->input('role'),
                'added_by_user_id' => $user->id,
                'added_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->withErrors(['user_email' => 'This user is already an owner of this venue.']);
            }
            throw $e;
        }

        return back()->with('success', "User '{$userToAdd->name}' has been added as a {$request->input('role')}.");
    }

    /**
     * Update an owner's role (primary owner only).
     */
    public function updateOwnerRole(Request $request, Venue $venue)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:co_owner,manager',
        ]);

        // Check if user is primary owner
        try {
            $primaryOwner = $venue->owners()->wherePivot('role', 'primary')->first();
            $isPrimaryOwner = $primaryOwner && $primaryOwner->id === $user->id;
        } catch (\Exception $e) {
            // Fallback to legacy check
            $isPrimaryOwner = ($venue->user_id === $user->id) || 
                            ($venue->owner_id && $venue->owner_type === User::class && $venue->owner_id === $user->id);
        }

        if (!$isPrimaryOwner && !$user->hasRole(['admin', 'superuser'])) {
            abort(403, 'Only the primary owner or admin can update owner roles.');
        }

        $userIdToUpdate = $request->input('user_id');

        // Prevent changing primary owner role
        try {
            $ownerToUpdate = $venue->owners()->where('user_id', $userIdToUpdate)->first();
            if ($ownerToUpdate && $ownerToUpdate->pivot->role === 'primary') {
                return back()->withErrors(['error' => 'Cannot change the role of the primary owner.']);
            }
        } catch (\Exception $e) {
            // Continue if check fails
        }

        // Update the role
        try {
            $venue->owners()->updateExistingPivot($userIdToUpdate, [
                'role' => $request->input('role'),
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update owner role: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Owner role updated successfully.');
    }

    /**
     * Notify venue owners when a new ownership request is submitted.
     */
    private function notifyVenueOwnersOfRequest(Venue $venue, VenueOwnerRequest $request, $requester)
    {
        // Get all current owners from the new many-to-many relationship
        $owners = $venue->owners;

        // If no owners in new table, fall back to legacy user_id/owner_id
        if ($owners->isEmpty()) {
            // Legacy: use user_id or owner relationship
            if ($venue->user_id) {
                $legacyUser = User::find($venue->user_id);
                if ($legacyUser) {
                    $owners = collect([$legacyUser]);
                }
            } elseif ($venue->owner_id && $venue->owner_type) {
                $ownerModel = $venue->owner;
                if ($ownerModel && isset($ownerModel->user_id)) {
                    $legacyUser = User::find($ownerModel->user_id);
                    if ($legacyUser) {
                        $owners = collect([$legacyUser]);
                    }
                }
            }
        }

        foreach ($owners as $owner) {
            if (!$owner || !$owner->email) {
                continue;
            }

            try {
                // Send email notification
                Mail::to($owner->email)->send(
                    new \App\Mail\VenueOwnershipRequestMail($venue, $request, $requester)
                );
            } catch (\Exception $e) {
                Log::error("Failed to send venue ownership request email to {$owner->email}: " . $e->getMessage());
            }
        }
    }
}
