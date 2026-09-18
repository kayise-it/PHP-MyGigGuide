<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\ClientAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refresh last access (and first-seen once) for a signed-in API user.
 * Guests are left alone. Writes are throttled on the user model.
 */
class RecordClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? $request->user('sanctum');

        if ($user instanceof User) {
            $access = ClientAccess::fromRequest($request);
            $user->recordAccess($access['client'], $access['platform']);
        }

        return $next($request);
    }
}
