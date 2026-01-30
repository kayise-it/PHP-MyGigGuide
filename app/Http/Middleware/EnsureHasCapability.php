<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Venue;

class EnsureHasCapability
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $capability = null): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $routeName = Route::currentRouteName();

        // If capability was provided as a middleware parameter, use it
        $requiredCapability = $capability;

        // Otherwise resolve from config using the current route name
        if (! $requiredCapability && $routeName) {
            $requiredCapability = config("capabilities.routes.{$routeName}");
        }

        // Special case: venue owners (including co-owners/managers) can always
        // manage their own venue, even if their role capabilities change.
        // This prevents capability config mistakes from locking out legitimate owners.
        if ($routeName && str_starts_with($routeName, 'venues.')) {
            $route = $request->route();
            $routeVenue = $route?->parameter('venue');

            if ($routeVenue instanceof Venue) {
                try {
                    $isOwner = $routeVenue->isOwnedBy($user->id);

                    // #region agent log
                    @file_put_contents(
                        '/var/www/mygigguide/.cursor/debug.log',
                        json_encode([
                            'sessionId' => 'debug-session',
                            'runId' => 'run1',
                            'hypothesisId' => 'VENUE_CAP',
                            'location' => 'EnsureHasCapability.php:venue-ownership-check',
                            'message' => 'Venue capability check with ownership',
                            'data' => [
                                'route_name' => $routeName,
                                'venue_id' => $routeVenue->id,
                                'user_id' => $user->id,
                                'is_owner' => $isOwner,
                                'required_capability' => $requiredCapability,
                            ],
                            'timestamp' => (int) round(microtime(true) * 1000),
                        ])."\n",
                        FILE_APPEND | LOCK_EX
                    );
                    // #endregion

                    if ($isOwner) {
                        // Owners bypass capability gating for their own venue
                        return $next($request);
                    }
                } catch (\Throwable $e) {
                    // If ownership check fails, fall through to capability logic
                }
            }
        }

        // If no capability configured, allow access (no-op)
        if (! $requiredCapability) {
            return $next($request);
        }

        // Use Laratrust "can" / permission check
        if (! $user->can($requiredCapability)) {
            // #region agent log
            @file_put_contents(
                '/var/www/mygigguide/.cursor/debug.log',
                json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'CAP_DENY',
                    'location' => 'EnsureHasCapability.php:deny',
                    'message' => 'Capability denied',
                    'data' => [
                        'user_id' => $user->id,
                        'route_name' => $routeName,
                        'required_capability' => $requiredCapability,
                    ],
                    'timestamp' => (int) round(microtime(true) * 1000),
                ])."\n",
                FILE_APPEND | LOCK_EX
            );
            // #endregion

            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

