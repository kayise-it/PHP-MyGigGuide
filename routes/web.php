<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganiserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\FeaturePurchaseController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\NotificationController;

// Public venue creation routes (MUST come before admin routes)
Route::get('/venues/create', [VenueController::class, 'create'])->name('venues.create');
Route::post('/venues', [VenueController::class, 'store'])->name('venues.store');
Route::post('/venues/quick', [VenueController::class, 'quickStore'])->name('venues.quick-store');
Route::get('/api/venues/search', [VenueController::class, 'search'])->name('api.venues.search');
Route::get('/api/venues/{venue}', [VenueController::class, 'showApi'])->name('api.venues.show');

// Venue ownership request routes (MUST come before /venues/{venue} route to avoid route conflicts)
Route::middleware(['auth', 'capability'])->group(function () {
    Route::get('/venues/my-requests', [VenueController::class, 'myVenueRequests'])->name('venues.my-requests');
    Route::post('/venues/request-ownership', [VenueController::class, 'requestOwnership'])->name('venues.request-ownership');
    
    // Venue owner management routes (only for venue owners)
    Route::post('/venues/{venue}/approve-request/{venueOwnerRequest}', [VenueController::class, 'approveRequest'])->name('venues.approve-request');
    Route::post('/venues/{venue}/reject-request/{venueOwnerRequest}', [VenueController::class, 'rejectRequest'])->name('venues.reject-request');
    Route::post('/venues/{venue}/add-owner', [VenueController::class, 'addOwner'])->name('venues.add-owner');
    Route::put('/venues/{venue}/update-owner-role', [VenueController::class, 'updateOwnerRole'])->name('venues.update-owner-role');
    Route::delete('/venues/{venue}/remove-owner', [VenueController::class, 'removeOwner'])->name('venues.remove-owner');
});

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/test-auth', function () {
    return view('test-auth');
})->name('test-auth');
// About page
Route::view('/about', 'about')->name('about');

// Legal pages
Route::view('/popia', 'popia')->name('popia');
Route::view('/terms', 'terms')->name('terms');

// Venue selector demo
Route::view('/venue-selector-demo', 'venue-selector-demo')->name('venue-selector-demo');

// User selector demo
Route::view('/user-selector-demo', 'user-selector-demo')->name('user-selector-demo');

// Contact routes
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

// Mark notification as read and redirect (auth required)
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/{id}', [NotificationController::class, 'readAndRedirect'])->name('notifications.read');
});

// Protected routes (require login)
Route::middleware(['auth', 'capability'])->group(function () {
    // Event routes
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}/rate', [EventController::class, 'rate'])->name('events.rate');
    Route::get('/events/{event}/calendar', [EventController::class, 'calendar'])->name('events.calendar');
});

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Public artist listing/show routes
Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');
Route::get('/artists/{artist}', [ArtistController::class, 'show'])->name('artists.show');

// Protected artist actions
Route::middleware(['auth', 'capability'])->group(function () {
    Route::get('/artists/{artist}/dispute', [ArtistController::class, 'dispute'])->name('artist.dispute');
});
Route::get('/venues/{venue}', [VenueController::class, 'show'])->name('venues.show');
Route::get('/organisers/{organiser}', [OrganiserController::class, 'show'])->name('organisers.show');

// Protected routes (require login)
Route::middleware(['auth', 'capability'])->group(function () {
    // Artist routes
    Route::get('/artists/create', [ArtistController::class, 'create'])->name('artists.create');
    Route::post('/artists', [ArtistController::class, 'store'])->name('artists.store');
    Route::get('/artists/{artist}/edit', [ArtistController::class, 'edit'])->name('artists.edit');
    Route::put('/artists/{artist}', [ArtistController::class, 'update'])->name('artists.update');
    Route::delete('/artists/{artist}', [ArtistController::class, 'destroy'])->name('artists.destroy');
    Route::post('/artists/quick', [ArtistController::class, 'quickStore'])->name('artists.quick-store');

    // Venue routes (protected - require login for management)
    Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/{venue}/edit', [VenueController::class, 'edit'])->name('venues.edit');
    Route::put('/venues/{venue}', [VenueController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{venue}', [VenueController::class, 'destroy'])->name('venues.destroy');

    // Organiser routes
    Route::get('/organisers', [OrganiserController::class, 'index'])->name('organisers.index');
    Route::get('/organisers/create', [OrganiserController::class, 'create'])->name('organisers.create');
    Route::post('/organisers', [OrganiserController::class, 'store'])->name('organisers.store');
    Route::get('/organisers/{organiser}/edit', [OrganiserController::class, 'edit'])->name('organisers.edit');
    Route::put('/organisers/{organiser}', [OrganiserController::class, 'update'])->name('organisers.update');
    Route::delete('/organisers/{organiser}', [OrganiserController::class, 'destroy'])->name('organisers.destroy');

    // Rating routes
    Route::resource('ratings', RatingController::class);
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Facebook OAuth routes
Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook'])->name('facebook.login');
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback'])->name('facebook.callback');

// Facebook Data Deletion Callback (required for Facebook app compliance)
// GET for testing/verification, POST for actual Facebook callbacks
Route::match(['get', 'post'], '/auth/facebook/data-deletion', [AuthController::class, 'handleFacebookDataDeletion'])->name('facebook.data-deletion');

// Password Reset routes
Route::get('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');

// Convenience route for Venue Owner registration (SEO/links)
Route::get('/venue-owner/register', function () {
    // redirect to the standard registration page with role preselected
    return redirect()->route('register', ['role' => 'venue_owner']);
})->name('venue-owner.register');

// Email verification routes
Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Account activation routes
Route::get('/activate/{user}/{token}', [App\Http\Controllers\ActivationController::class, 'activate'])->name('activation.activate');
Route::post('/resend-activation', [App\Http\Controllers\ActivationController::class, 'resendActivation'])->name('activation.resend');
Route::get('/activation-required', function () {
    return view('auth.activation-required');
})->name('activation.required');

// Protected routes (require login)
Route::middleware('auth')->group(function () {
    // Map pages
    Route::get('/map', [HomeController::class, 'map'])->name('map');
    Route::get('/test-map', [HomeController::class, 'testMap'])->name('test-map');
    
    // Paid feature purchase flow
    Route::get('/boost/checkout', [FeaturePurchaseController::class, 'create'])->name('features.checkout');
    Route::post('/boost/purchase', [FeaturePurchaseController::class, 'store'])->name('features.purchase');
    
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/artist', [DashboardController::class, 'artistDashboard'])->name('dashboard.artist');
    Route::get('/dashboard/organiser', [DashboardController::class, 'organiserDashboard'])->name('dashboard.organiser');
    Route::get('/dashboard/venue-owner', [DashboardController::class, 'venueOwnerDashboard'])->name('dashboard.venue-owner');
    Route::get('/dashboard/admin', [DashboardController::class, 'adminDashboard'])->name('dashboard.admin');
    Route::get('/dashboard/user', [DashboardController::class, 'userDashboard'])->name('dashboard.user');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Favorite routes (toggle favorites with AJAX)
    Route::post('/favorites/events/{event}/toggle', [FavoriteController::class, 'toggleEvent'])->name('favorites.events.toggle');
    Route::post('/favorites/venues/{venue}/toggle', [FavoriteController::class, 'toggleVenue'])->name('favorites.venues.toggle');
    Route::post('/favorites/artists/{artist}/toggle', [FavoriteController::class, 'toggleArtist'])->name('favorites.artists.toggle');
    Route::post('/favorites/organisers/{organiser}/toggle', [FavoriteController::class, 'toggleOrganiser'])->name('favorites.organisers.toggle');
    Route::get('/favorites/check', [FavoriteController::class, 'checkFavorites'])->name('favorites.check');

    // Rating routes
    Route::post('/ratings', [App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');
    Route::post('/reviews/load-more', [App\Http\Controllers\RatingController::class, 'loadMoreReviews'])->name('reviews.load-more');
});

// API routes for testing
Route::get('/api/user', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::check() ? Auth::user() : null,
    ]);
});

// Temporary maintenance route to create email_templates table if migrations cannot run
Route::get('/internal/dev/create-email-templates-table', function () {
    if (!Schema::hasTable('email_templates')) {
        Schema::create('email_templates', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->longText('body_html');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    // Seed initial claim_invitation template if missing
    if (!DB::table('email_templates')->where('key', 'claim_invitation')->exists()) {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Your Artist Profile</title>
</head>
<body>
    <h1>🎵 Claim Your Artist Profile!</h1>
    <p>Hi {{ $contactName ?? 'there' }},</p>
    <p>
        We're excited to let you know that we've created a {{ $entityType ?? 'artist' }} profile for
        <strong>{{ $entityName }}</strong> on My Gig Guide, South Africa's premier music discovery platform.
    </p>
    <p>
        <a href="{{ $registerUrl }}">Click here to claim your profile</a>.
    </p>
    <p>
        Once you claim your profile, you'll be able to manage your information, upload photos and videos,
        promote events, and connect with the South African music community.
    </p>
    <p>Best regards,<br>The My Gig Guide Team</p>
</body>
</html>
HTML;

        DB::table('email_templates')->insert([
            'key' => 'claim_invitation',
            'name' => 'Claim Your Artist / Venue Profile',
            'subject' => '🎵 Claim Your Artist Profile on My Gig Guide!',
            'description' => 'Invitation for artists / venues / organisers to claim their auto-created profile.',
            'body_html' => $html,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return 'email_templates table is present.';
});

// Temporary migration route - REMOVE AFTER USE
Route::get('/run-migrations', function () {
    try {
        // Use Laravel's database connection
        $dbName = config('database.connections.mysql.database');
        $dbHost = config('database.connections.mysql.host');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        
        $pdo = new PDO("mysql:host={$dbHost};port=3306;dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $result = ['success' => true, 'messages' => []];
        $result['messages'][] = '✅ Database connection successful!';
        $result['messages'][] = "Database: {$dbName}";

        // Check current tables
        $stmt = $pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $result['messages'][] = 'Current tables: '.count($tables);
        
        // Check if venue_owner tables exist
        $hasVenueOwners = in_array('venue_owners', $tables);
        $hasVenueOwnerRequests = in_array('venue_owner_requests', $tables);
        $result['venue_owners_table_exists'] = $hasVenueOwners;
        $result['venue_owner_requests_table_exists'] = $hasVenueOwnerRequests;

        if (!$hasVenueOwners || !$hasVenueOwnerRequests) {
            $result['messages'][] = '⚠️ Venue owner tables are missing. Running migrations...';
            
            // Run migrations
            $output = [];
            $returnCode = 0;
            $cwd = base_path();
            exec("cd {$cwd} && php artisan migrate --force 2>&1", $output, $returnCode);

            $result['migration_output'] = implode("\n", $output);
            $result['migration_success'] = $returnCode === 0;

            if ($returnCode === 0) {
                $result['messages'][] = '✅ Migrations completed successfully!';

                // Verify tables again
                $stmt = $pdo->query('SHOW TABLES');
                $newTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $result['messages'][] = 'Tables after migration: '.count($newTables);
                $result['venue_owners_table_exists_after'] = in_array('venue_owners', $newTables);
                $result['venue_owner_requests_table_exists_after'] = in_array('venue_owner_requests', $newTables);
            } else {
                $result['messages'][] = '❌ Migrations failed with return code: '.$returnCode;
            }
        } else {
            $result['messages'][] = '✅ All venue owner tables already exist!';
        }

        return response()->json($result, 200, [], JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// Include admin routes (MUST come after public routes)
require __DIR__.'/admin.php';
