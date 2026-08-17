<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Http\Resources\Api\V1\Concerns\SerializesPageOwnership;
use App\Http\Resources\Api\V1\Concerns\SerializesRatingSummary;
use App\Services\ArtistPageService;
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
            'phone_number' => $this->phone_number,
            'contact_email' => $this->contact_email,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'profile_picture_url' => self::publicStorageUrl($this->profile_picture),
            'gallery_urls' => self::publicStorageUrls(is_array($this->gallery) ? $this->gallery : []),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'youtube_videos' => $this->whenLoaded('youtubeVideos', function () {
                return $this->youtubeVideos->map(fn ($v) => [
                    'youtube_url' => $v->youtube_url,
                    'youtube_video_id' => $v->youtube_video_id,
                    'title' => $v->title,
                    'order' => $v->order,
                ])->values()->all();
            }),
            'upcoming_events' => EventResource::collection($this->whenLoaded('upcomingEvents')),
            'posted_events' => EventResource::collection($this->whenLoaded('postedEvents')),
            'recent_events' => EventResource::collection($this->whenLoaded('recentEvents')),
            'events_count' => (int) ($this->events_count ?? 0),
            'detail_url' => route('artists.show', $this->resource),
            'rating_summary' => $this->ratingSummary($request),
            'can_edit_videos' => $request->user('sanctum') instanceof \App\Models\User
                && app(ArtistPageService::class)->userCanEditPage(
                    $request->user('sanctum'),
                    $this->resource
                ),
            'can_edit_profile' => $request->user('sanctum') instanceof \App\Models\User
                && app(ArtistPageService::class)->userCanEditPage(
                    $request->user('sanctum'),
                    $this->resource
                ),
            'snapscan_code' => $request->user('sanctum') instanceof \App\Models\User
                && app(ArtistPageService::class)->userCanEditPage(
                    $request->user('sanctum'),
                    $this->resource
                )
                ? $this->snapscan_code
                : null,
            ...$this->pageOwnershipFields($request),
        ];
    }
}
