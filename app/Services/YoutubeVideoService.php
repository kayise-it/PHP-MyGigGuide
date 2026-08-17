<?php

namespace App\Services;

use App\Models\YoutubeVideo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YoutubeVideoService
{
    /**
     * Fetch a video title from YouTube's oEmbed endpoint (no API key required).
     */
    public function fetchTitle(string $videoId): ?string
    {
        $videoId = trim($videoId);
        if ($videoId === '') {
            return null;
        }

        return Cache::remember(
            'youtube_title_'.$videoId,
            now()->addDays(30),
            function () use ($videoId) {
                try {
                    $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                        'url' => 'https://www.youtube.com/watch?v='.$videoId,
                        'format' => 'json',
                    ]);

                    if (! $response->successful()) {
                        return null;
                    }

                    $title = $response->json('title');

                    return is_string($title) && trim($title) !== '' ? trim($title) : null;
                } catch (\Throwable $e) {
                    Log::debug('YouTube oEmbed title fetch failed', [
                        'video_id' => $videoId,
                        'error' => $e->getMessage(),
                    ]);

                    return null;
                }
            }
        );
    }

    /**
     * Create a linked YouTube row and store the fetched title when available.
     */
    public function createFor(Model $videoable, string $url, int $order): ?YoutubeVideo
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $videoId = YoutubeVideo::extractVideoId($url);
        if (! $videoId) {
            return null;
        }

        return YoutubeVideo::create([
            'videoable_type' => $videoable->getMorphClass(),
            'videoable_id' => $videoable->id,
            'youtube_url' => $url,
            'youtube_video_id' => $videoId,
            'title' => $this->fetchTitle($videoId),
            'order' => $order,
        ]);
    }

    /**
     * Fill missing titles on existing rows (safe to run repeatedly).
     */
    public function backfillMissingTitles(int $limit = 100): int
    {
        $updated = 0;

        YoutubeVideo::query()
            ->where(function ($query) {
                $query->whereNull('title')->orWhere('title', '');
            })
            ->orderBy('id')
            ->limit($limit)
            ->each(function (YoutubeVideo $video) use (&$updated) {
                $title = $this->fetchTitle($video->youtube_video_id);
                if ($title === null) {
                    return;
                }

                $video->update(['title' => $title]);
                $updated++;
            });

        return $updated;
    }
}
