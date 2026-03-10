<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ActivationController extends Controller
{
    /**
     * Send activation email to user
     */
    public function sendActivationEmail(User $user)
    {
        // Generate activation token
        $token = Str::random(64);

        // Store token in user record (you might want to create a separate table for this)
        $user->update([
            'activation_token' => $token,
            'activation_token_expires_at' => Carbon::now()->addHours(24),
        ]);

        // Generate activation URL
        $activationUrl = URL::temporarySignedRoute(
            'activation.activate',
            now()->addHours(24),
            ['token' => $token, 'user' => $user->id]
        );

        // Send activation email
        Mail::to($user->email)->send(new AccountActivation($user, $activationUrl));

        return true;
    }

    /**
     * Activate user account
     */
    public function activate(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'user' => 'required|integer|exists:users,id',
        ]);

        $user = User::findOrFail($request->user);

        // Check if token is valid and not expired
        if (! $user->activation_token ||
            $user->activation_token !== $request->token ||
            ! $user->activation_token_expires_at ||
            $user->activation_token_expires_at->isPast()) {

            return redirect()->route('login')->with('error', 'Invalid or expired activation link.');
        }

        // Activate the user
        $user->update([
            'email_verified_at' => now(),
            'activation_token' => null,
            'activation_token_expires_at' => null,
        ]);

        return redirect()->route('login')->with('success', 'Your account has been activated successfully! You can now log in.');
    }

    /**
     * Resend activation email
     */
    public function resendActivation(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return back()->with('error', 'This account is already activated.');
        }

        $this->sendActivationEmail($user);

        return back()->with('success', 'Activation email has been sent. Please check your inbox.');
    }
}
