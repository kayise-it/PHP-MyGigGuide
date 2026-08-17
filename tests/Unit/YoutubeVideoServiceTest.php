<?php

namespace Tests\Unit;

use App\Models\Artist;
use App\Models\YoutubeVideo;
use App\Services\YoutubeVideoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YoutubeVideoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_from_url_stores_oembed_title(): void
    {
        Http::fake([
            'www.youtube.com/oembed*' => Http::response([
                'title' => 'Never Gonna Give You Up',
            ], 200),
        ]);

        $artist = Artist::create([
            'stage_name' => 'Title Test '.uniqid(),
            'real_name' => 'Title Test',
            'genre' => 'Rock',
            'contact_email' => 'title-test+'.uniqid().'@example.local',
            'user_id' => null,
            'claim_status' => 'approved',
        ]);

        $video = YoutubeVideo::createFromUrl(
            $artist,
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            0
        );

        $this->assertNotNull($video);
        $this->assertSame('Never Gonna Give You Up', $video->title);
        $this->assertSame('Never Gonna Give You Up', $video->display_title);
    }

    public function test_display_title_falls_back_when_missing(): void
    {
        $video = new YoutubeVideo([
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'title' => null,
        ]);

        $this->assertSame('Watch on YouTube', $video->display_title);
    }
}
