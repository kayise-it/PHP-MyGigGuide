<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Venue;
use App\Models\Organiser;
use App\Mail\EmailVerificationMail;
use App\Mail\ArtistClaimWarningMail;
use App\Mail\PendingClaimNoticeMail;
use App\Services\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;
use App\Rules\UniqueNormalizedName;
use App\Helpers\NameNormalizer;

class AuthController extends Controller
{
    protected ClaimService $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     * Login is by username only (not email).
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $input = trim($request->input('username'));
        $user = User::whereRaw('LOWER(username) = ?', [strtolower($input)])->first();

        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'AuthController::login:user_lookup', 'message' => 'user lookup', 'data' => ['input_length' => strlen((string) $input), 'user_found' => $user !== null, 'user_id' => $user?->id, 'username' => $user?->username, 'email' => $user ? substr($user->email ?? '', 0, 5) . '...' : null], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'D']) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
        $credentials = $user
            ? ['username' => $user->username, 'password' => $request->password]
            : $request->only('username', 'password');
        $remember = $request->boolean('remember');

        $attemptOk = Auth::attempt($credentials, $remember);
        // #region agent log
        $storedHash = $user ? $user->getRawOriginal('password') : null;
        $hashCheck = $user && $storedHash ? Hash::check($request->password, $storedHash) : false;
        @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'AuthController::login:attempt', 'message' => 'attempt result', 'data' => ['attempt_ok' => $attemptOk, 'Hash_check_typed_vs_stored' => $hashCheck], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'B']) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
        if ($attemptOk) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user has verified email
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Check for unclaimed artist
                $unclaimedArtist = Artist::whereNull('user_id')
                    ->whereRaw('LOWER(contact_email) = ?', [strtolower($user->email)])
                    ->first();

                if ($unclaimedArtist) {
                    return redirect()->route('verification.notice')
                        ->with('error', 'Please verify your email to claim your artist profile and access your account. A verification email has been sent to ' . $user->email . '.');
                }

                return redirect()->route('verification.notice')
                    ->with('error', 'Please verify your email address before logging in. A verification email has been sent to ' . $user->email . '.');
            }

            // Handle continue parameter to redirect user back to where they were
            if ($request->has('continue')) {
                return redirect($request->get('continue'));
            }

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        // Preselect role if provided via query (e.g. ?role=venue_owner)
        $requestedRole = request('role');
        if ($requestedRole && in_array($requestedRole, ['user', 'artist', 'organiser', 'venue_owner'])) {
            // Flash old input so the select shows the desired value
            $old = session('_old_input', []);
            $old['role'] = $requestedRole;
            session()->flash('_old_input', $old);
        }

        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        // #region agent log
        @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'AuthController.php:register:entry','message'=>'User registration entry','data'=>['request_email'=>$request->input('email'),'request_role'=>$request->input('role'),'has_continue'=>$request->has('continue')],'timestamp'=>now()->timestamp*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'G'])."\n", FILE_APPEND | LOCK_EX);
        // #endregion

        // If this is coming from auth modal, we have simplified fields
        if ($request->has('continue')) {
            $request->validate([
                'name' => ['required', 'string', 'max:255', UniqueNormalizedName::forUser()],
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'continue' => 'sometimes|string',
            ]);

            // Generate username from email
            $username = str_replace('@', '', $request->email);
            $originalUsername = $username;
            $counter = 1;

            // Ensure username is unique
            while (User::where('username', $username)->exists()) {
                $username = $originalUsername.$counter;
                $counter++;
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Check for unclaimed entities with matching email (artists, venues, organisers)
            $claimResults = $this->claimService->initiateClaimsForUser($user);
            $unclaimedEntities = $this->claimService->findUnclaimedByEmail($request->email);
            $unclaimedArtist = $unclaimedEntities->first(fn($e) => $e instanceof Artist);
            $hasPendingEntities = !empty($claimResults['pending']);

            if ($hasPendingEntities) {
                $entityNames = collect($claimResults['pending'])->pluck('name')->join(', ');
                $entityCount = count($claimResults['pending']);
                $gracePeriodEnabled = config('artist_claims.enable_grace_period', false);
                $gracePeriodHours = config('artist_claims.grace_period_hours', 48);
                
                $gracePeriodText = $gracePeriodEnabled 
                    ? " Your claim(s) will be processed after " . Carbon::now()->addHours($gracePeriodHours)->diffForHumans() . "."
                    : "";
                
                $successMessage = "Account created! We found {$entityCount} profile(s) ({$entityNames}) linked to your email. Please verify your email to claim your profile(s).{$gracePeriodText}";
            } else {
                // Assign default role as 'user'
                $user->addRole('user');
                $successMessage = 'Account created successfully! Please verify your email to complete registration.';
            }

            // Create user folder and settings
            $user->getOrCreateFolderSettings();

            // Don't auto-login - require email verification first
            // Store email in session for resend functionality
            session(['pending_verification_email' => $user->email]);
            
            // Send verification email with artist info
            Mail::to($user->email)->send(new EmailVerificationMail($user, $unclaimedArtist));

            // Redirect to verification notice
            return redirect()->route('verification.notice')->with('success', $successMessage);
        }

        // Handle full registration with additional fields
        $request->validate([
            'name' => ['required', 'string', 'max:255', UniqueNormalizedName::forUser()],
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:user,artist,organiser,venue_owner',
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Check for unclaimed entities with matching email (artists, venues, organisers)
        $claimResults = $this->claimService->initiateClaimsForUser($user);
        $unclaimedEntities = $this->claimService->findUnclaimedByEmail($request->email);
        $unclaimedArtist = $unclaimedEntities->first(fn($e) => $e instanceof Artist);
        $hasPendingEntities = !empty($claimResults['pending']);

        // Assign selected role (but artist will be added on verification if pending artist exists)
        $user->addRole($request->role);

        // Create user folder and settings
        $user->getOrCreateFolderSettings();

        // Don't auto-login - require email verification first
        // Store email in session for resend functionality
        session(['pending_verification_email' => $user->email]);
        
        // Send verification email with artist info
        Mail::to($user->email)->send(new EmailVerificationMail($user, $unclaimedArtist));

        $roleName = ucfirst(str_replace('_', ' ', $request->role));
        
        if ($hasPendingEntities) {
            $entityNames = collect($claimResults['pending'])->pluck('name')->join(', ');
            $entityCount = count($claimResults['pending']);
            $gracePeriodEnabled = config('artist_claims.enable_grace_period', false);
            $gracePeriodHours = config('artist_claims.grace_period_hours', 48);
            
            $gracePeriodText = $gracePeriodEnabled 
                ? " Your claim(s) will be processed after " . Carbon::now()->addHours($gracePeriodHours)->diffForHumans() . "."
                : "";
            $successMessage = "Account created as {$roleName}! We found {$entityCount} profile(s) ({$entityNames}) linked to your email. Please verify your email to claim your profile(s).{$gracePeriodText}";
        } else {
            $successMessage = "Account created as {$roleName}! Please verify your email to complete registration.";
        }

        // Redirect to verification notice
        return redirect()->route('verification.notice')->with('success', $successMessage);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect the user to the Facebook authentication page.
     */
    public function redirectToFacebook(Request $request)
    {
        // Validate Facebook credentials are configured
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            Log::error('Facebook OAuth: Missing credentials', [
                'client_id_set' => !empty($clientId),
                'client_secret_set' => !empty($clientSecret),
            ]);

            return redirect()->route('login')
                ->with('error', 'Facebook login is not properly configured. Please contact support.');
        }

        // Store the requested role in session if provided
        if ($request->has('role')) {
            session(['facebook_registration_role' => $request->get('role')]);
        }

        // Store continue URL if provided
        if ($request->has('continue')) {
            session(['facebook_continue_url' => $request->get('continue')]);
        }

        try {
            return Socialite::driver('facebook')
                ->scopes(['email'])
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Facebook OAuth redirect error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $clientId ? 'set' : 'missing',
            ]);

            return redirect()->route('login')
                ->with('error', 'Unable to connect to Facebook. Please try again later.');
        }
    }

    /**
     * Handle the Facebook callback.
     */
    public function handleFacebookCallback(Request $request)
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // Check if Facebook returned an email
            if (!$facebookUser->getEmail()) {
                return redirect()->route('login')
                    ->with('error', 'Unable to retrieve email from Facebook. Please ensure your Facebook account has a verified email address.');
            }

            // Check if user already exists with this Facebook ID
            $user = User::where('auth_provider', 'facebook')
                ->where('auth_provider_id', $facebookUser->getId())
                ->first();

            // If user doesn't exist, check if email exists
            if (!$user) {
                $user = User::where('email', $facebookUser->getEmail())->first();

                if ($user) {
                    // User exists with email but not linked to Facebook - link it
                    $user->update([
                        'auth_provider' => 'facebook',
                        'auth_provider_id' => $facebookUser->getId(),
                    ]);
                } else {
                    // Create new user
                    $username = str_replace('@', '', $facebookUser->getEmail());
                    $originalUsername = $username;
                    $counter = 1;

                    // Ensure username is unique
                    while (User::where('username', $username)->exists()) {
                        $username = $originalUsername . $counter;
                        $counter++;
                    }

                    // Ensure name is unique (normalized)
                    $name = $this->ensureUniqueName($facebookUser->getName(), 'users', 'name');

                    // Get requested role from session or default to 'user'
                    $role = session('facebook_registration_role', 'user');
                    session()->forget('facebook_registration_role');

                    $user = User::create([
                        'name' => $name,
                        'username' => $username,
                        'email' => $facebookUser->getEmail(),
                        'password' => Hash::make(uniqid('', true)), // Random password for OAuth users
                        'auth_provider' => 'facebook',
                        'auth_provider_id' => $facebookUser->getId(),
                        'profile_picture' => $facebookUser->getAvatar(),
                        'email_verified_at' => now(), // Facebook emails are verified
                    ]);

                    // Check for unclaimed entities with matching email (artists, venues, organisers)
                    $claimResults = $this->claimService->initiateClaimsForUser($user);
                    $hasPendingEntities = !empty($claimResults['pending']);

                    if ($hasPendingEntities) {
                        // Assign appropriate roles based on claimed entity types
                        $claimedTypes = collect($claimResults['pending'])->pluck('type')->unique();
                        
                        if ($claimedTypes->contains('artist')) {
                            $user->addRole('artist');
                        }
                        if ($claimedTypes->contains('organiser')) {
                            $user->addRole('organiser');
                        }
                        if ($claimedTypes->contains('venue')) {
                            $user->addRole('venue_owner');
                        }
                        
                        // Also assign the originally selected role if different
                        if (!$user->hasRole($role)) {
                            $user->addRole($role);
                        }
                    } else {
                        // Assign selected role or default to 'user'
                        $user->addRole($role);
                    }

                    // Create user folder and settings
                    $user->getOrCreateFolderSettings();
                }
            }

            // Log the user in
            Auth::login($user, true);

            $request->session()->regenerate();

            // Handle continue URL if stored in session
            $continueUrl = session('facebook_continue_url');
            session()->forget('facebook_continue_url');

            if ($continueUrl) {
                return redirect($continueUrl);
            }

            return redirect()->intended(route('dashboard'));

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('Facebook OAuth: Invalid state exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')
                ->with('error', 'Facebook authentication session expired. Please try again.');
        } catch (\Laravel\Socialite\Two\ProviderException $e) {
            Log::error('Facebook OAuth: Provider exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => method_exists($e, 'getResponse') ? $e->getResponse() : null,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')
                ->with('error', 'Facebook authentication failed. Please check your Facebook app configuration.');
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'unknown';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'no response';
            
            Log::error('Facebook OAuth: HTTP client exception', [
                'message' => $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = 'Unable to connect to Facebook.';
            if ($statusCode === 400) {
                $errorMessage = 'Invalid Facebook app configuration. Please contact support.';
            } elseif ($statusCode === 401) {
                $errorMessage = 'Facebook authentication failed. Please check your Facebook app credentials.';
            }

            return redirect()->route('login')
                ->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Facebook OAuth: Unexpected error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
            ]);
            return redirect()->route('login')
                ->with('error', 'An unexpected error occurred during Facebook authentication. Please try again.');
        }
    }

    /**
     * Handle Facebook data deletion callback.
     * This endpoint is called by Facebook when a user requests data deletion.
     * 
     * Facebook sends a POST request with a signed_request parameter.
     * The signed_request contains the user's Facebook ID that needs to be deleted.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleFacebookDataDeletion(Request $request)
    {
        try {
            // Handle GET requests for testing/verification
            if ($request->isMethod('GET')) {
                return response()->json([
                    'status' => 'active',
                    'url' => route('facebook.data-deletion'),
                    'message' => 'Facebook Data Deletion Callback is active. This endpoint accepts POST requests from Facebook.',
                ]);
            }

            // Facebook sends data in signed_request format
            $signedRequest = $request->input('signed_request');
            
            if (!$signedRequest) {
                Log::warning('Facebook data deletion: No signed_request received', [
                    'request_data' => $request->all(),
                ]);
                return response()->json([
                    'url' => route('facebook.data-deletion'),
                    'confirmation_code' => null,
                ], 400);
            }

            // Parse the signed_request
            // Format: base64url(JSON).base64url(HMAC_SHA256(JSON))
            $parts = explode('.', $signedRequest, 2);
            if (count($parts) !== 2) {
                Log::warning('Facebook data deletion: Invalid signed_request format');
                return response()->json([
                    'url' => route('facebook.data-deletion'),
                    'confirmation_code' => null,
                ], 400);
            }

            $signature = $parts[0];
            $payload = $parts[1];

            // Verify the signature using Facebook app secret
            $appSecret = config('services.facebook.client_secret');
            if ($appSecret) {
                $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $appSecret, true));
                $expectedSignature = strtr(rtrim($expectedSignature, '='), '+/', '-_');
                
                if (!hash_equals($signature, $expectedSignature)) {
                    Log::warning('Facebook data deletion: Invalid signature', [
                        'received_signature' => substr($signature, 0, 20) . '...',
                        'expected_signature' => substr($expectedSignature, 0, 20) . '...',
                    ]);
                    return response()->json([
                        'url' => route('facebook.data-deletion'),
                        'confirmation_code' => null,
                    ], 400);
                }
            } else {
                Log::warning('Facebook data deletion: App secret not configured');
            }

            // Decode the payload
            $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

            if (!$data || !isset($data['user_id'])) {
                Log::warning('Facebook data deletion: Invalid payload or missing user_id', [
                    'data' => $data,
                    'payload_decoded' => base64_decode(strtr($payload, '-_', '+/')),
                ]);
                return response()->json([
                    'url' => route('facebook.data-deletion'),
                    'confirmation_code' => null,
                ], 400);
            }

            $facebookUserId = $data['user_id'];
            
            // Generate a unique confirmation code
            $confirmationCode = 'DEL_' . strtoupper(substr(md5($facebookUserId . config('app.key') . time()), 0, 10));

            // Find user by Facebook provider ID
            $user = User::where('auth_provider', 'facebook')
                ->where('auth_provider_id', $facebookUserId)
                ->first();

            if ($user) {
                // Log the deletion request
                Log::info('Facebook data deletion requested', [
                    'facebook_user_id' => $facebookUserId,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'confirmation_code' => $confirmationCode,
                ]);

                // Delete the user and all associated data
                // Note: Facebook requires immediate response, so deletion happens synchronously
                // In production, you might want to queue this for better performance
                try {
                    $this->deleteUserData($user);
                    
                    Log::info('Facebook data deletion completed', [
                        'facebook_user_id' => $facebookUserId,
                        'user_id' => $user->id,
                        'confirmation_code' => $confirmationCode,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Facebook data deletion failed', [
                        'facebook_user_id' => $facebookUserId,
                        'user_id' => $user->id ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Still return confirmation code even if deletion fails
                    // Facebook needs a response, and we've logged the error
                }
            } else {
                Log::info('Facebook data deletion: User not found', [
                    'facebook_user_id' => $facebookUserId,
                ]);
                // Still return a confirmation code even if user doesn't exist
                // This is expected behavior - user may have already been deleted
            }

            // Facebook requires this exact response format
            return response()->json([
                'url' => route('facebook.data-deletion'),
                'confirmation_code' => $confirmationCode,
            ])->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            Log::error('Facebook data deletion error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // Always return valid JSON response, even on error
            return response()->json([
                'url' => route('facebook.data-deletion'),
                'confirmation_code' => null,
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Delete user and all associated data.
     * This method contains the deletion logic similar to ProfileController.
     * 
     * @param User $user
     * @return void
     */
    private function deleteUserData(User $user)
    {
        DB::beginTransaction();
        try {
            // Get all events owned by user
            $events = $user->events()->get();
            
            // Get events owned by artist
            if ($user->artist) {
                $artistEvents = Event::where('owner_type', Artist::class)
                    ->where('owner_id', $user->artist->id)
                    ->get();
                $events = $events->merge($artistEvents);
            }
            
            // Get events owned by organiser
            if ($user->organiser) {
                $events = $events->merge($user->organiser->events()->get());
            }
            
            $events = $events->unique('id');

            // Delete all events and their images
            foreach ($events as $event) {
                if ($event->poster && Storage::disk('public')->exists($event->poster)) {
                    Storage::disk('public')->delete($event->poster);
                }
                if ($event->gallery) {
                    $gallery = is_array($event->gallery) ? $event->gallery : json_decode($event->gallery, true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $image) {
                            if ($image && Storage::disk('public')->exists($image)) {
                                Storage::disk('public')->delete($image);
                            }
                        }
                    }
                }
                $event->delete();
            }

            // Get all venues owned by user
            $venues = $user->venues()->get();
            foreach ($venues as $venue) {
                if ($venue->main_picture && Storage::disk('public')->exists($venue->main_picture)) {
                    Storage::disk('public')->delete($venue->main_picture);
                }
                if ($venue->venue_gallery) {
                    $gallery = is_array($venue->venue_gallery) ? $venue->venue_gallery : json_decode($venue->venue_gallery, true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $image) {
                            if ($image && Storage::disk('public')->exists($image)) {
                                Storage::disk('public')->delete($image);
                            }
                        }
                    }
                }
                $venue->delete();
            }

            // Delete user's profile picture
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Detach all relationships
            $user->favoriteEvents()->detach();
            $user->favoriteVenues()->detach();
            $user->favoriteArtists()->detach();
            $user->favoriteOrganisers()->detach();
            $user->ratings()->delete();

            // Delete role-specific profiles
            if ($user->artist) {
                $artist = $user->artist;
                if ($artist->profile_picture && Storage::disk('public')->exists($artist->profile_picture)) {
                    Storage::disk('public')->delete($artist->profile_picture);
                }
                if ($artist->gallery) {
                    $gallery = is_array($artist->gallery) ? $artist->gallery : json_decode($artist->gallery, true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $image) {
                            if ($image && Storage::disk('public')->exists($image)) {
                                Storage::disk('public')->delete($image);
                            }
                        }
                    }
                }
                $artist->delete();
            }

            if ($user->organiser) {
                $organiser = $user->organiser;
                if ($organiser->logo && Storage::disk('public')->exists($organiser->logo)) {
                    Storage::disk('public')->delete($organiser->logo);
                }
                $organiser->delete();
            }

            // Delete the user
            $user->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ensure a name is unique by appending a suffix if necessary.
     * Used for social login where users can't control their display name.
     *
     * @param string $name
     * @param string $table
     * @param string $column
     * @param int|null $excludeId
     * @return string
     */
    private function ensureUniqueName(string $name, string $table, string $column, ?int $excludeId = null): string
    {
        $normalizedInput = NameNormalizer::normalize($name);
        
        // Get all existing names from the table
        $query = DB::table($table)->select(['id', $column]);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        $existingRecords = $query->get();

        // Check for normalized duplicates
        $isDuplicate = false;
        foreach ($existingRecords as $record) {
            if (NameNormalizer::normalize($record->{$column}) === $normalizedInput) {
                $isDuplicate = true;
                break;
            }
        }

        if (!$isDuplicate) {
            return $name;
        }

        // Append a number suffix to make it unique
        $counter = 2;
        $baseName = $name;
        
        while (true) {
            $candidateName = "{$baseName} ({$counter})";
            $normalizedCandidate = NameNormalizer::normalize($candidateName);
            
            $found = false;
            foreach ($existingRecords as $record) {
                if (NameNormalizer::normalize($record->{$column}) === $normalizedCandidate) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return $candidateName;
            }
            
            $counter++;
        }
    }
}
