<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class YoutubeVideo extends Model
{
    protected $fillable = [
        'videoable_type',
        'videoable_id',
        'youtube_url',
        'youtube_video_id',
        'title',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the parent videoable model (Event, Artist, or Venue).
     */
    public function videoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Extract YouTube video ID from various URL formats.
     */
    public static function extractVideoId(string $url): ?string
    {
        // Remove whitespace
        $url = trim($url);

        // Pattern to match various YouTube URL formats
        $patterns = [
            // youtube.com/watch?v=VIDEO_ID
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            // youtube.com/embed/VIDEO_ID
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            // youtu.be/VIDEO_ID
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            // youtube.com/v/VIDEO_ID
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
            // youtube.com/watch?feature=player_embedded&v=VIDEO_ID
            '/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/',
            // Just the video ID itself (11 characters)
            '/^([a-zA-Z0-9_-]{11})$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Generate YouTube embed URL.
     */
    public function getEmbedUrlAttribute(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->youtube_video_id}";
    }

    /**
     * Generate YouTube thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return "https://img.youtube.com/vi/{$this->youtube_video_id}/maxresdefault.jpg";
    }
}

