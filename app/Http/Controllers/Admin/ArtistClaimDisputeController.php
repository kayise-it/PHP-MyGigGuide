<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArtistClaimDisputeController extends Controller
{
    /**
     * Display a listing of all artist claim disputes.
     */
    public function index(Request $request)
    {
        try {
            // Check if required columns exist before querying
            $columns = DB::getSchemaBuilder()->getColumnListing('artists');
            $hasRequiredColumns = in_array('pending_claim_user_id', $columns) && 
                                 in_array('claim_status', $columns) && 
                                 in_array('dispute_raised', $columns);

            if (!$hasRequiredColumns) {
                // Return empty result if columns don't exist
                $disputes = \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 20);
                return view('admin.artist-disputes.index', compact('disputes'))
                    ->with('info', 'Artist claim disputes feature requires database migration. Please run migrations.');
            }

            $query = Artist::with(['pendingClaimUser'])
                ->where(function($q) {
                    // Show all items that are disputed OR have pending claims
                    $q->where('dispute_raised', true)
                      ->orWhere('claim_status', 'disputed')
                      ->orWhere(function($pendingQ) {
                          // Items with pending claims
                          $pendingQ->whereNotNull('pending_claim_user_id')
                                   ->where('claim_status', 'pending');
                      });
                });

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'disputed') {
                    $query->where('dispute_raised', true);
                } else {
                    $query->where('claim_status', $request->status);
                }
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('stage_name', 'like', "%{$search}%")
                      ->orWhere('contact_email', 'like', "%{$search}%")
                      ->orWhereHas('pendingClaimUser', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $disputes = $query->orderByDesc('dispute_raised_at')
                              ->orderByDesc('pending_claim_at')
                              ->orderByDesc('created_at')
                              ->paginate(20)->withQueryString();

            return view('admin.artist-disputes.index', compact('disputes'));
        } catch (\Exception $e) {
            Log::error('Error loading artist disputes: ' . $e->getMessage());
            
            // Return empty result on error
            $disputes = \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 20);
            return view('admin.artist-disputes.index', compact('disputes'))
                ->with('error', 'Error loading disputes: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified dispute.
     */
    public function show(Artist $artist)
    {
        $artist->load('pendingClaimUser');
        
        // Allow viewing disputes even without pending claims
        // Just verify it's actually disputed
        if (!$artist->dispute_raised && $artist->claim_status !== 'disputed') {
            return redirect()->route('admin.artist-disputes.index')
                ->with('error', 'This artist is not in a disputed state.');
        }

        return view('admin.artist-disputes.show', compact('artist'));
    }

    /**
     * Approve a claim and link the artist to the user.
     */
    public function approve(Request $request, Artist $artist)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if (!$artist->pending_claim_user_id) {
            return redirect()->route('admin.artist-disputes.index')
                ->with('error', 'No pending claim found for this artist.');
        }

        $user = User::findOrFail($artist->pending_claim_user_id);

        // Check if user already has an artist profile - if so, unlink it first (make it unclaimed)
        $existingArtist = $user->artist;
        if ($existingArtist && $existingArtist->id !== $artist->id) {
            // Unlink the existing artist (set user_id to null, making it unclaimed)
            $existingArtist->update(['user_id' => null]);
            $existingArtist->clearClaimData();
        }

        // Link the artist to the user
        $artist->update([
            'user_id' => $user->id,
            'claim_status' => 'approved',
            'dispute_raised' => false,
            'pending_claim_user_id' => null,
            'pending_claim_at' => null,
            'dispute_raised_at' => null,
        ]);

        // Assign artist role if not already assigned
        if (!$user->hasRole('artist')) {
            $user->addRole('artist');
        }

        // Send approval email to user
        try {
            Mail::to($user->email)->send(new \App\Mail\ClaimApprovedMail($artist, $user));
        } catch (\Exception $e) {
            Log::error('Failed to send claim approval email: ' . $e->getMessage());
        }

        // Log admin action
        Log::info("Admin approved artist claim: Artist ID {$artist->id} claimed by User ID {$user->id}", [
            'artist' => $artist->stage_name,
            'user' => $user->email,
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.artist-disputes.index')
            ->with('success', "Claim approved! Artist '{$artist->stage_name}' has been linked to user '{$user->name}' ({$user->email}).");
    }

    /**
     * Reject a claim and clear the pending claim.
     */
    public function reject(Request $request, Artist $artist)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if (!$artist->pending_claim_user_id) {
            return redirect()->route('admin.artist-disputes.index')
                ->with('error', 'No pending claim found for this artist.');
        }

        $user = User::findOrFail($artist->pending_claim_user_id);

        // Clear the pending claim
        $artist->update([
            'claim_status' => 'rejected',
            'pending_claim_user_id' => null,
            'pending_claim_at' => null,
            'dispute_raised' => false,
            'dispute_raised_at' => null,
        ]);

        // Send rejection email to user
        try {
            Mail::to($user->email)->send(new \App\Mail\ClaimRejectedMail($artist, $user, $request->rejection_reason));
        } catch (\Exception $e) {
            Log::error('Failed to send claim rejection email: ' . $e->getMessage());
        }

        // Log admin action
        Log::info("Admin rejected artist claim: Artist ID {$artist->id} claim from User ID {$user->id}", [
            'artist' => $artist->stage_name,
            'user' => $user->email,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.artist-disputes.index')
            ->with('success', "Claim rejected. Artist '{$artist->stage_name}' remains unclaimed.");
    }

    /**
     * Clear a dispute (if raised in error).
     */
    public function clearDispute(Artist $artist)
    {
        if (!$artist->dispute_raised) {
            return redirect()->route('admin.artist-disputes.index')
                ->with('error', 'No dispute found for this artist.');
        }

        // Clear dispute but keep pending claim
        $artist->update([
            'dispute_raised' => false,
            'dispute_raised_at' => null,
            'claim_status' => 'pending',
        ]);

        // If grace period passed or disabled, approve immediately
        $gracePeriodEnabled = config('artist_claims.enable_grace_period', false);
        if (!$gracePeriodEnabled || ($artist->grace_period_ends_at && Carbon::now()->gte($artist->grace_period_ends_at))) {
            if ($artist->pending_claim_user_id) {
                $user = User::findOrFail($artist->pending_claim_user_id);
                
                // Check if user already has an artist profile - if so, unlink it first (make it unclaimed)
                $existingArtist = $user->artist;
                if ($existingArtist && $existingArtist->id !== $artist->id) {
                    // Unlink the existing artist (set user_id to null, making it unclaimed)
                    $existingArtist->update(['user_id' => null]);
                    $existingArtist->clearClaimData();
                }
                
                $artist->update([
                    'user_id' => $user->id,
                    'claim_status' => 'approved',
                    'pending_claim_user_id' => null,
                    'pending_claim_at' => null,
                ]);

                if (!$user->hasRole('artist')) {
                    $user->addRole('artist');
                }

                return redirect()->route('admin.artist-disputes.index')
                    ->with('success', "Dispute cleared and claim approved! Artist '{$artist->stage_name}' has been linked to user '{$user->name}'.");
            }
        }

        return redirect()->route('admin.artist-disputes.index')
            ->with('success', "Dispute cleared. Claim is now pending approval.");
    }
}
