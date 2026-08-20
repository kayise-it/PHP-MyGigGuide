<?php

use App\Http\Controllers\Admin\ArtistManagementController;
use App\Http\Controllers\Admin\ArtistClaimDisputeController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\Admin\GenreManagementController;
use App\Http\Controllers\Admin\OrganiserManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VenueManagementController;
use App\Http\Controllers\Admin\PaidFeatureController;
use App\Http\Controllers\Admin\FeatureProgramController;
use App\Http\Controllers\Admin\FeaturePackageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UnclaimedArtistController;
use App\Http\Controllers\Admin\UnclaimedController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\DuplicateManagementController;
use App\Http\Controllers\Admin\CapabilityManagementController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\MailAccountController;
use App\Http\Controllers\Admin\PollAdminController;

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Public admin routes (no middleware)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // User Management
        Route::resource('users', UserManagementController::class);
        Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/api/users/search', [UserManagementController::class, 'search'])->name('api.users.search');

        // Email verification management
        Route::patch('users/{user}/verify-email', [UserManagementController::class, 'verifyEmail'])->name('users.verify-email');
        Route::patch('users/{user}/unverify-email', [UserManagementController::class, 'unverifyEmail'])->name('users.unverify-email');
        Route::patch('users/{user}/update-email', [UserManagementController::class, 'updateEmail'])->name('users.update-email');
        Route::post('users/{user}/send-password-reset', [UserManagementController::class, 'sendPasswordReset'])->name('users.send-password-reset');

        // Event Management
        Route::resource('events', EventManagementController::class);
        Route::patch('events/{event}/toggle-status', [EventManagementController::class, 'toggleStatus'])->name('events.toggle-status');

        // Venue Management
        // IMPORTANT: define literal routes (e.g. export) before the resource {venue} route to avoid collisions.
        Route::get('venues/export', [VenueManagementController::class, 'export'])->name('venues.export');
        Route::get('venues/import/template', [VenueManagementController::class, 'downloadImportTemplate'])->name('venues.import-template');
        Route::post('venues/import', [VenueManagementController::class, 'importVenues'])->name('venues.import');
        Route::post('venues/import-csv', [VenueManagementController::class, 'import'])->name('venues.import-csv');
        Route::post('venues/bulk-action', [VenueManagementController::class, 'bulkAction'])->name('venues.bulk-action');
        Route::patch('venues/{venue}/toggle-status', [VenueManagementController::class, 'toggleStatus'])->name('venues.toggle-status');
        // Some hosts block HTTP DELETE; provide POST fallback for destroy
        Route::post('venues/{venue}/delete', [VenueManagementController::class, 'destroy'])->name('venues.destroy.post');
        // Venue role management (superuser only)
        Route::post('venues/{venue}/add-role', [VenueManagementController::class, 'addRole'])->name('venues.add-role');
        Route::post('venues/{venue}/remove-role', [VenueManagementController::class, 'removeRole'])->name('venues.remove-role');
        Route::post('venues/{venue}/update-role', [VenueManagementController::class, 'updateRole'])->name('venues.update-role');
        Route::resource('venues', VenueManagementController::class);

        // Artist Management
        // IMPORTANT: define literal routes (e.g. export) before the resource {artist} route to avoid collisions.
        Route::get('artists/export', [ArtistManagementController::class, 'export'])->name('artists.export');
        Route::get('artists/import/template', [ArtistManagementController::class, 'downloadImportTemplate'])->name('artists.import-template');
        Route::post('artists/import', [ArtistManagementController::class, 'import'])->name('artists.import');
        Route::patch('artists/{artist}/toggle-status', [ArtistManagementController::class, 'toggleStatus'])->name('artists.toggle-status');
        Route::resource('artists', ArtistManagementController::class);

        // Unclaimed Items (unified dashboard for all entity types)
        Route::prefix('unclaimed')->name('unclaimed.')->group(function () {
            Route::get('/', [UnclaimedController::class, 'index'])->name('index');
            Route::post('/bulk-send-email', [UnclaimedController::class, 'bulkSendClaimEmails'])->name('bulk-send-email');
            Route::get('/{type}/{id}/edit', [UnclaimedController::class, 'edit'])->name('edit')
                ->where('type', 'artist|venue|event|organiser');
            Route::put('/{type}/{id}', [UnclaimedController::class, 'update'])->name('update')
                ->where('type', 'artist|venue|event|organiser');
            Route::delete('/{type}/{id}', [UnclaimedController::class, 'destroy'])->name('destroy')
                ->where('type', 'artist|venue|event|organiser');
            Route::post('/{type}/{id}/send-invite', [UnclaimedController::class, 'sendClaimInvite'])->name('send-invite')
                ->where('type', 'artist|venue|event|organiser');
            Route::post('/{type}/{id}/link-user', [UnclaimedController::class, 'linkToUser'])->name('link-user')
                ->where('type', 'artist|venue|event|organiser');
            Route::post('/{type}/{id}/approve-claim', [UnclaimedController::class, 'approvePendingClaim'])->name('approve-claim')
                ->where('type', 'artist|venue|event|organiser');
            Route::post('/{type}/{id}/reject-claim', [UnclaimedController::class, 'rejectPendingClaim'])->name('reject-claim')
                ->where('type', 'artist|venue|event|organiser');
            Route::post('/{type}/{id}/check-link-conflict', [UnclaimedController::class, 'checkLinkConflict'])->name('check-link-conflict')
                ->where('type', 'artist|venue|event|organiser');
        });

        // Legacy route redirects for backward compatibility
        Route::get('unclaimed-artists', fn() => redirect()->route('admin.unclaimed.index', ['type' => 'artist']))->name('unclaimed-artists.index');
        Route::get('unclaimed-artists/{artist}/edit', fn($artist) => redirect()->route('admin.unclaimed.edit', ['type' => 'artist', 'id' => $artist]))->name('unclaimed-artists.edit');

        // Artist Claim Disputes
        Route::get('artist-disputes', [ArtistClaimDisputeController::class, 'index'])->name('artist-disputes.index');
        Route::get('artist-disputes/{artist}', [ArtistClaimDisputeController::class, 'show'])->name('artist-disputes.show');
        Route::post('artist-disputes/{artist}/approve', [ArtistClaimDisputeController::class, 'approve'])->name('artist-disputes.approve');
        Route::post('artist-disputes/{artist}/reject', [ArtistClaimDisputeController::class, 'reject'])->name('artist-disputes.reject');
        Route::post('artist-disputes/{artist}/clear-dispute', [ArtistClaimDisputeController::class, 'clearDispute'])->name('artist-disputes.clear-dispute');

        // Organiser Management
        Route::resource('organisers', OrganiserManagementController::class);
        Route::patch('organisers/{organiser}/toggle-status', [OrganiserManagementController::class, 'toggleStatus'])->name('organisers.toggle-status');

        // Genre Management
        Route::resource('genres', GenreManagementController::class);
        Route::patch('genres/{genre}/toggle-status', [GenreManagementController::class, 'toggleStatus'])->name('genres.toggle-status');

        // Category Management
        Route::resource('categories', CategoryManagementController::class);
        Route::patch('categories/{category}/toggle-status', [CategoryManagementController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Polls (station contexts incl. mix938)
        Route::patch('polls/{poll}/close', [PollAdminController::class, 'close'])->name('polls.close');
        Route::resource('polls', PollAdminController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        // Paid Features CRUD
        Route::resource('paid-features', PaidFeatureController::class)->parameters([
            'paid-features' => 'paidFeature',
        ]);

        // Feature Programs
        Route::resource('feature-programs', FeatureProgramController::class)->parameters([
            'feature-programs' => 'featureProgram',
        ]);

        // Feature Packages nested under a Paid Feature
        Route::prefix('paid-features/{paidFeature}')->group(function () {
            Route::get('packages', [FeaturePackageController::class, 'index'])->name('feature-packages.index');
            Route::get('packages/create', [FeaturePackageController::class, 'create'])->name('feature-packages.create');
            Route::post('packages', [FeaturePackageController::class, 'store'])->name('feature-packages.store');
            Route::get('packages/{featurePackage}/edit', [FeaturePackageController::class, 'edit'])->name('feature-packages.edit');
            Route::put('packages/{featurePackage}', [FeaturePackageController::class, 'update'])->name('feature-packages.update');
            Route::delete('packages/{featurePackage}', [FeaturePackageController::class, 'destroy'])->name('feature-packages.destroy');
        });

        // Site Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/toggle', [SettingsController::class, 'toggle'])->name('settings.toggle');

        // Email Templates
        Route::resource('email-templates', EmailTemplateController::class);

        // Mail Accounts Management
        Route::resource('mail-accounts', MailAccountController::class);

        // Capabilities (role ↔ permission matrix)
        Route::get('capabilities', [CapabilityManagementController::class, 'index'])->name('capabilities.index');
        Route::post('capabilities', [CapabilityManagementController::class, 'update'])->name('capabilities.update');

        // Duplicate Names Management
        Route::prefix('duplicates')->name('duplicates.')->group(function () {
            Route::get('/', [DuplicateManagementController::class, 'index'])->name('index');
            Route::get('/summary', [DuplicateManagementController::class, 'summary'])->name('summary');
            Route::get('/{type}/{id}', [DuplicateManagementController::class, 'show'])->name('show')
                ->where('type', 'users|artists|organisers|venues');
            Route::post('/{type}/{id}/rename', [DuplicateManagementController::class, 'rename'])->name('rename')
                ->where('type', 'users|artists|organisers|venues');
        });
    });
});
