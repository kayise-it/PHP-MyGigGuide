<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Http\Resources\Api\V1\Concerns\SerializesPageOwnership;
use App\Http\Resources\Api\V1\Concerns\SerializesRatingSummary;
use App\Services\VenuePageService;
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
            'google_place_id' => $this->google_place_id,
            'capacity' => $this->capacity,
            'website' => $this->website,
            'phone_number' => $this->phone_number,
            'contact_email' => $this->contact_email,
            'main_picture_url' => self::publicStorageUrl($this->main_picture),
            'gallery_urls' => self::publicStorageUrls(
                is_array($this->venue_gallery) ? $this->venue_gallery : []
            ),
            'youtube_videos' => $this->whenLoaded('youtubeVideos', function () {
                return $this->youtubeVideos->map(fn ($v) => [
                    'youtube_url' => $v->youtube_url,
                    'youtube_video_id' => $v->youtube_video_id,
                    'title' => $v->title,
                    'order' => $v->order,
                ])->values()->all();
            }),
            'upcoming_events' => EventResource::collection($this->whenLoaded('upcomingEvents')),
            'recent_events' => EventResource::collection($this->whenLoaded('recentEvents')),
            'events_count' => (int) ($this->events_count ?? 0),
            'detail_url' => route('venues.show', $this->resource),
            'rating_summary' => $this->ratingSummary($request),
            'can_edit_videos' => $request->user('sanctum') instanceof \App\Models\User
                && app(VenuePageService::class)->userCanEditPage(
                    $request->user('sanctum'),
                    $this->resource
                ),
            'can_edit_profile' => $request->user('sanctum') instanceof \App\Models\User
                && app(VenuePageService::class)->userCanEditPage(
                    $request->user('sanctum'),
                    $this->resource
                ),
            ...$this->pageOwnershipFields($request),
        ];
    }
}
