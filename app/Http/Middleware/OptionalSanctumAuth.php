<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve a Sanctum bearer token when present — guests stay guests (no 401).
 *
 * Public detail routes need this so resources can expose owner-only fields
 * (e.g. can_edit_profile) when the app sends Authorization: Bearer …
 */
class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('sanctum') === null && $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($request->bearerToken());

            if ($accessToken?->tokenable) {
                auth()->guard('sanctum')->setUser($accessToken->tokenable);
            }
        }

        return $next($request);
    }
}
