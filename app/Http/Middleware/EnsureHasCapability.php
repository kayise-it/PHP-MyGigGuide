<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // Venue routes: co-ownership is allowed. Any row in venue_owners (primary, co_owner, manager)
        // grants access to edit/update/delete that venue — no role capability required.
        if ($routeName && str_starts_with($routeName, 'venues.')) {
            $venueId = null;
            $route = $request->route();
            $venueParam = $route?->parameter('venue');
            if ($venueParam instanceof Venue) {
                $venueId = (int) $venueParam->id;
            } elseif (is_numeric($venueParam)) {
                $venueId = (int) $venueParam;
            }
            // Fallback: venue ID from URL when param not yet bound (e.g. /venues/754/edit)
            if ($venueId === null && preg_match('#^venues/(\d+)(/|$)#', $request->path(), $m)) {
                $venueId = (int) $m[1];
            }
            if ($venueId !== null) {
                try {
                    $isCoOwnerOrOwner = DB::table('venue_owners')
                        ->where('venue_id', $venueId)
                        ->where('user_id', (int) $user->id)
                        ->exists();
                    if ($isCoOwnerOrOwner) {
                        return $next($request);
                    }
                } catch (\Throwable $e) {
                    // table missing or DB error: fall through to capability check
                }
                if ($venueParam instanceof Venue) {
                    try {
                        if ($venueParam->isOwnedBy((int) $user->id)) {
                            return $next($request);
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
        }

        // If no capability configured, allow access (no-op)
        if (! $requiredCapability) {
            return $next($request);
        }

        // Use Laratrust "can" / permission check
        if (! $user->can($requiredCapability)) {
            Log::warning('Capability denied', [
                'route' => $routeName,
                'user_id' => $user->id,
                'required' => $requiredCapability,
            ]);
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

