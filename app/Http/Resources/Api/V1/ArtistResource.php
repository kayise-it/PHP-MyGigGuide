<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    use ResolvesStorageUrl;

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
            'detail_url' => route('artists.show', $this->resource),
        ];
    }
}
