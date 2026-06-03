<?php

namespace App\Http\Controllers;

use App\Services\AppWebSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppWebSessionController extends Controller
{
    public function __construct(
        private readonly AppWebSessionService $webSession,
    ) {}

    /**
     * Exchange a one-time app token for a website session cookie.
     */
    public function consume(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'min:32', 'max:128'],
            'redirect' => ['nullable', 'string', 'max:2048'],
        ]);

        $user = $this->webSession->consume($validated['token']);

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('error', 'That sign-in link has expired or was already used. Open the app and try again.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        $redirect = $this->webSession->sanitizeRedirect($validated['redirect'] ?? null);

        return redirect()->to($redirect);
    }
}
