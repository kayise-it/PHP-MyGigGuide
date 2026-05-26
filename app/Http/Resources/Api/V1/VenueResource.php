<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Http\Resources\Api\V1\Concerns\SerializesPageOwnership;
use App\Http\Resources\Api\V1\Concerns\SerializesRatingSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    use ResolvesStorageUrl;
    use SerializesPageOwnership;
    use SerializesRatingSummary;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'city' => $this->city,
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'capacity' => $this->capacity,
            'website' => $this->website,
            'main_picture_url' => self::publicStorageUrl($this->main_picture),
            'upcoming_events' => EventResource::collection($this->whenLoaded('upcomingEvents')),
            'events_count' => (int) ($this->events_count ?? 0),
            'detail_url' => route('venues.show', $this->resource),
            'rating_summary' => $this->ratingSummary($request),
            ...$this->pageOwnershipFields($request),
        ];
    }
}
