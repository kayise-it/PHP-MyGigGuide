<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    use ResolvesStorageUrl;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gallery = is_array($this->gallery) ? $this->gallery : [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'date' => $this->date?->toIso8601String(),
            'time' => $this->time ? $this->time->format('H:i:s') : null,
            'price' => $this->price,
            'ticket_url' => $this->ticket_url,
            'status' => $this->status,
            'legacy_category' => $this->category,
            'poster_url' => self::publicStorageUrl($this->poster),
            'gallery_urls' => self::publicStorageUrls($gallery),
            'venue' => VenueResource::make($this->whenLoaded('venue')),
            'artists' => ArtistResource::collection($this->whenLoaded('artists')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'youtube_videos' => $this->whenLoaded('youtubeVideos', function () {
                return $this->youtubeVideos->map(fn ($v) => [
                    'youtube_url' => $v->youtube_url,
                    'youtube_video_id' => $v->youtube_video_id,
                    'title' => $v->title,
                    'order' => $v->order,
                ])->values()->all();
            }),
            'detail_url' => route('events.show', $this->resource),
        ];
    }
}
