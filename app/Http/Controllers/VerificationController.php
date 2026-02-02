<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Models\Artist;
use App\Models\Venue;
use App\Models\Organiser;
use App\Services\ClaimService;
use Carbon\Carbon;

class VerificationController extends Controller
{
    protected ClaimService $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * Show the email verification notice.
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, $id, $hash)
    {
        // #region agent log
        @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode(['location'=>'VerificationController.php:verify:entry','message'=>'Email verification entry','data'=>['user_id'=>$id],'timestamp'=>now()->timestamp*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'H'])."\n", FILE_APPEND | LOCK_EX);
        // #endregion

        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')->with('error', 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            // User already verified, login if not already logged in
            if (!Auth::check()) {
                Auth::login($user);
            }
            return redirect()->route('profile.show')->with('success', 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Check for unclaimed entities with matching email (artists, venues, organisers)
        $claimResults = $this->claimService->autoClaimOnVerification($user);
        
        $hasApproved = !empty($claimResults['approved']);
        $hasPending = !empty($claimResults['pending']);
        $hasDisputed = !empty($claimResults['disputed']);

        if ($hasApproved || $hasPending || $hasDisputed) {
            $claimMessage = "Email verified successfully! ";
            
            // Build message about approved claims
            if ($hasApproved) {
                $approvedNames = collect($claimResults['approved'])->pluck('name')->join(', ');
                $claimMessage .= "Your profile(s) ({$approvedNames}) have been claimed. ";
            }
            
            // Build message about pending claims
            if ($hasPending) {
                $pendingNames = collect($claimResults['pending'])->pluck('name')->join(', ');
                $firstPending = $claimResults['pending'][0];
                $remainingTime = isset($firstPending['grace_period_ends']) 
                    ? Carbon::parse($firstPending['grace_period_ends'])->diffForHumans()
                    : 'soon';
                $claimMessage .= "Your profile(s) ({$pendingNames}) are pending. Claims will be processed {$remainingTime}. ";
            }
            
            // Build message about disputed claims
            if ($hasDisputed) {
                $disputedNames = collect($claimResults['disputed'])->pluck('name')->join(', ');
                $claimMessage .= "Your profile(s) ({$disputedNames}) have disputes under review by admin. ";
            }
            
            $claimMessage .= "Welcome to My Gig Guide!";

            // Login the user
            Auth::login($user);
            
            // Clear pending verification email from session
            session()->forget('pending_verification_email');

            return redirect()->route('profile.show')->with('success', $claimMessage);
        }

        // Login the user
        Auth::login($user);
        
        // Clear pending verification email from session
        session()->forget('pending_verification_email');

        return redirect()->route('profile.show')->with('success', 'Email verified successfully! Welcome to My Gig Guide!');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        $user = $request->user();
        
        // If user is not logged in, they might be trying to resend from registration
        if (!$user) {
            // Prefer explicit email from the request, otherwise fall back to the session value
            $email = $request->input('email') ?? session('pending_verification_email');

            if (!$email) {
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Please enter the email address you used when signing up.');
            }

            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'We could not find an account with that email address. Please double-check and try again.');
            }
        }
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile.show')->with('info', 'Email already verified.');
        }

        // Persist the email so subsequent resends still work for logged-out users
        session(['pending_verification_email' => $user->email]);

        // Send verification email for the user account only (no artist-claim framing).
        // Claiming of artist/venue profiles still happens when they click the link (verify() -> autoClaimOnVerification).
        Mail::to($user->email)->send(new EmailVerificationMail($user, null));

        return back()->with('success', 'Verification email sent to ' . $user->email);
    }
}
