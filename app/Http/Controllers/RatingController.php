<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Store a newly created rating.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rateable_type' => 'required|string|in:App\\Models\\Artist,App\\Models\\Event,App\\Models\\Venue,App\\Models\\Organiser',
                'rateable_id' => 'required|integer',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = auth()->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to rate',
                ], 401);
            }

            // Get the rateable model
            $rateableClass = $request->rateable_type;
            $rateable = $rateableClass::findOrFail($request->rateable_id);

            // Check if user already rated this item
            $existingRating = Rating::where('user_id', $user->id)
                ->where('rateable_type', $rateableClass)
                ->where('rateable_id', $request->rateable_id)
                ->first();

            if ($existingRating) {
                // Update existing rating
                $existingRating->update([
                    'rating' => $request->rating,
                    'review' => $request->review,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Rating updated successfully',
                    'rating' => $existingRating->load('user'),
                ]);
            }

            // Create new rating
            $rating = Rating::create([
                'user_id' => $user->id,
                'rateable_type' => $rateableClass,
                'rateable_id' => $request->rateable_id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'rating' => $rating->load('user'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Rating submit error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating. Please try again.',
            ], 500);
        }
    }

    /**
     * Load more reviews for a model
     */
    public function loadMoreReviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_id' => 'required|integer',
            'model_type' => 'required|string|in:App\\Models\\Artist,App\\Models\\Event,App\\Models\\Venue,App\\Models\\Organiser',
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
            ], 422);
        }

        // Get the model instance
        $modelClass = $request->model_type;
        $model = $modelClass::findOrFail($request->model_id);

        // Get reviews with pagination
        $reviews = $model->ratings()
            ->with('user')
            ->latest()
            ->skip($request->offset)
            ->take($request->limit)
            ->get()
            ->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'review' => $rating->review,
                    'user' => [
                        'name' => $rating->user->name,
                    ],
                    'created_at' => $rating->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
