<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Generate reset token
        $token = Str::random(64);
        
        // Delete any existing tokens for this user
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Send reset email
        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $token));
            
            return back()->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            return back()->withErrors(['email' => 'Failed to send reset email. Please try again later.']);
        }
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle password reset.
     */
    public function reset(Request $request)
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'PasswordResetController::reset:entry', 'message' => 'reset entry', 'data' => ['email' => $request->email, 'token_length' => strlen((string) $request->token), 'password_length' => strlen((string) $request->password)], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'C']) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if token exists and is valid
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            // #region agent log
            @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'PasswordResetController::reset:no_record', 'message' => 'no reset record', 'data' => [], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'C']) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Check if token matches (hashed)
        if (!Hash::check($request->token, $resetRecord->token)) {
            // #region agent log
            @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'PasswordResetController::reset:token_mismatch', 'message' => 'token mismatch', 'data' => [], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'C']) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Check if token is expired (24 hours)
        $tokenAge = Carbon::parse($resetRecord->created_at)->diffInHours(Carbon::now());
        if ($tokenAge > 24) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            // #region agent log
            @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'PasswordResetController::reset:token_expired', 'message' => 'token expired', 'data' => ['tokenAge_hours' => $tokenAge], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'C']) . "\n", FILE_APPEND | LOCK_EX);
            // #endregion
            return back()->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Update user password (User model has 'password' => 'hashed' cast, so assign plain password)
        $user = User::where('email', $request->email)->first();
        // #region agent log
        @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'PasswordResetController::reset:before_save', 'message' => 'before save', 'data' => ['user_id' => $user->id, 'username' => $user->username], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'A']) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion
        $user->password = $request->password;
        $user->save();
        // #region agent log
        $stored = $user->fresh()->getRawOriginal('password');
        $verify = Hash::check($request->password, $stored);
        @file_put_contents($logPath, json_encode(['timestamp' => time() * 1000, 'location' => 'PasswordResetController::reset:after_save', 'message' => 'after save', 'data' => ['user_id' => $user->id, 'stored_hash_prefix' => substr($stored ?? '', 0, 7), 'Hash_check_plain_vs_stored' => $verify], 'sessionId' => 'debug-session', 'runId' => 'reset-login', 'hypothesisId' => 'A']) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion

        // Delete the reset token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset! You can now login with your new password.');
    }
}


