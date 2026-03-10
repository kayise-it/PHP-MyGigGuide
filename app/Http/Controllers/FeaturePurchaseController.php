<?php

namespace App\Http\Controllers;

use App\Models\FeaturePurchase;
use App\Models\PaidFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeaturePurchaseController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'feature_id' => 'required|exists:paid_features,id',
            'featureable_type' => 'required|in:artist,venue,event',
            'featureable_id' => 'required|integer',
        ]);

        $feature = PaidFeature::findOrFail($request->feature_id);
        
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to purchase features.');
        }

        return view('features.checkout', [
            'feature' => $feature,
            'featureableId' => (int) $request->featureable_id,
            'featureableType' => $request->featureable_type,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feature_id' => 'required|exists:paid_features,id',
            'featureable_type' => 'required|in:artist,venue,event',
            'featureable_id' => 'required|integer',
        ]);

        $feature = PaidFeature::findOrFail($validated['feature_id']);

        $purchase = FeaturePurchase::create([
            'user_id' => Auth::id(),
            'paid_feature_id' => $feature->id,
            'featureable_type' => $validated['featureable_type'],
            'featureable_id' => $validated['featureable_id'],
            'price_cents_at_purchase' => $feature->price_cents,
            'currency' => $feature->currency,
            'status' => 'paid', // Placeholder: assume payment success for now
        ]);

        $purchase->activateNow();

        // Redirect back to the relevant page with a flag to show the modal
        $type = $validated['featureable_type'];
        $id = $validated['featureable_id'];
        if ($type === 'artist') {
            return redirect()->route('artists.show', $id)
                ->with('success', 'Your boost is now active.')
                ->with('show_boost_modal', true);
        }
        if ($type === 'venue') {
            return redirect()->route('venues.show', $id)
                ->with('success', 'Your boost is now active.')
                ->with('show_boost_modal', true);
        }
        if ($type === 'event') {
            return redirect()->route('events.show', $id)
                ->with('success', 'Your boost is now active.')
                ->with('show_boost_modal', true);
        }

        return redirect()->back()->with('success', 'Your boost is now active.')->with('show_boost_modal', true);
    }
}


