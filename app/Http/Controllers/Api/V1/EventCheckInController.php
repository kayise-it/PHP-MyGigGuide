<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Venue;
use App\Services\EventTonightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventCheckInController extends Controller
{
    public function __construct(
        private readonly EventTonightService $tonight,
    ) {}

    public function board(Request $request, Event $event): JsonResponse
    {
        return response()->json([
            'data' => $this->tonight->boardForEvent($event, $request->user('sanctum')),
        ]);
    }

    public function tonightAtVenue(Request $request, Venue $venue): JsonResponse
    {
        return response()->json([
            'data' => $this->tonight->tonightAtVenue($venue, $request->user('sanctum')),
        ]);
    }

    public function checkIn(Request $request, Event $event): JsonResponse
    {
        $this->tonight->checkIn($request->user(), $event);

        return response()->json([
            'data' => $this->tonight->boardForEvent($event, $request->user()),
        ], 201);
    }

    public function removeCheckIn(Request $request, Event $event): JsonResponse
    {
        $this->tonight->removeCheckIn($request->user(), $event);

        return response()->json([
            'data' => $this->tonight->boardForEvent($event, $request->user()),
        ]);
    }
}
