<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiPermission
{
    /** Crowd-source MVP: any logged-in member may list events or rate (curation later). */
    private const MEMBER_ROLES = [
        'user',
        'venue_owner',
        'organiser',
        'artist',
    ];

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Admin roles bypass Laratrust capability names (production superuser may not
        // have every permission row attached even though the website grants full access).
        if ($user->hasRole(['superuser', 'admin'])) {
            return $next($request);
        }

        if (in_array($permission, ['create-events', 'rate-content'], true)
            && $user->hasRole(self::MEMBER_ROLES)) {
            return $next($request);
        }

        if (! $user->can($permission)) {
            return response()->json([
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
