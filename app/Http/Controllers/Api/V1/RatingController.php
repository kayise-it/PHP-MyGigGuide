<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RatingResource;
use App\Services\ApiRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RatingController extends Controller
{
    public function __construct(
        private readonly ApiRatingService $ratings,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:events,artists,venues'],
            'id' => ['required', 'integer', 'min:1'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $rating = $this->ratings->store(
                user: $request->user(),
                type: $validated['type'],
                id: (int) $validated['id'],
                rating: (int) $validated['rating'],
                review: isset($validated['review']) ? trim((string) $validated['review']) : null,
            );
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json([
            'data' => new RatingResource($rating),
            'message' => 'Rating saved.',
        ]);
    }

    public function index(Request $request, string $type, int $id): JsonResponse
    {
        $validated = $request->validate([
            'offset' => ['sometimes', 'integer', 'min:0'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $offset = (int) ($validated['offset'] ?? 0);
        $limit = (int) ($validated['limit'] ?? 10);

        try {
            $reviews = $this->ratings->listReviews($type, $id, $offset, $limit);
            $total = $this->ratings->reviewCount($type, $id);
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json([
            'data' => RatingResource::collection($reviews),
            'meta' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($offset + $reviews->count()) < $total,
            ],
        ]);
    }
}
