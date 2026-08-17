<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ContentReportResource;
use App\Services\ContentReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContentReportController extends Controller
{
    public function __construct(
        private readonly ContentReportService $reports,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:events,artists,venues'],
            'id' => ['required', 'integer', 'min:1'],
            'category' => ['required', 'string', 'max:64'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $report = $this->reports->store(
                user: $request->user(),
                type: $validated['type'],
                id: (int) $validated['id'],
                category: $validated['category'],
                message: isset($validated['message']) ? (string) $validated['message'] : null,
            );
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json([
            'data' => new ContentReportResource($report),
            'message' => 'Report submitted. Thank you — we will review it.',
        ], 201);
    }
}
