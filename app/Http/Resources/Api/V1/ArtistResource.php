<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Http\Resources\Api\V1\Concerns\SerializesPageOwnership;
use App\Http\Resources\Api\V1\Concerns\SerializesRatingSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
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
            'stage_name' => $this->stage_name,
            'real_name' => $this->real_name,
            'genre' => $this->genre,
            'bio' => $this->bio,
            'profile_picture_url' => self::publicStorageUrl($this->profile_picture),
            'gallery_urls' => self::publicStorageUrls(is_array($this->gallery) ? $this->gallery : []),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'upcoming_events' => EventResource::collection($this->whenLoaded('upcomingEvents')),
            'events_count' => (int) ($this->events_count ?? 0),
            'detail_url' => route('artists.show', $this->resource),
            'rating_summary' => $this->ratingSummary($request),
            ...$this->pageOwnershipFields($request),
        ];
    }
}
