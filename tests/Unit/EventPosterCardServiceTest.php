<?php

namespace Tests\Unit;

use App\Services\EventPosterCardService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventPosterCardServiceTest extends TestCase
{
    public function test_portrait_card_for_poster_skips_landscape_and_square(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }

        Storage::fake('public');

        $service = app(EventPosterCardService::class);

        $wide = UploadedFile::fake()->image('wide.jpg', 1200, 600);
        $widePath = $wide->store('users/1/events/test/poster', 'public');
        $this->assertNull($service->portraitCardForPoster($widePath));

        $square = UploadedFile::fake()->image('square.jpg', 800, 800);
        $squarePath = $square->store('users/1/events/test/poster', 'public');
        $this->assertNull($service->portraitCardForPoster($squarePath));
    }

    public function test_portrait_card_for_poster_generates_for_portrait_only(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }

        Storage::fake('public');

        $poster = UploadedFile::fake()->image('tall.jpg', 400, 800);
        $stored = $poster->store('users/1/events/test/poster', 'public');

        $service = app(EventPosterCardService::class);
        $cardPath = $service->portraitCardForPoster($stored);

        $this->assertNotNull($cardPath);
        Storage::disk('public')->assertExists($cardPath);
    }

    public function test_generates_portrait_card_from_landscape_poster(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }

        Storage::fake('public');

        $poster = UploadedFile::fake()->image('wide.jpg', 1200, 600);
        $stored = $poster->store('users/1/events/test/poster', 'public');

        $service = app(EventPosterCardService::class);
        $cardPath = $service->generatePortraitCard($stored);

        $this->assertNotNull($cardPath);
        $this->assertNotSame($stored, $cardPath);
        Storage::disk('public')->assertExists($cardPath);

        $info = getimagesize(Storage::disk('public')->path($cardPath));
        $this->assertNotFalse($info);
        $this->assertSame(600, $info[0]);
        $this->assertSame(900, $info[1]);
    }

    public function test_generates_portrait_card_from_portrait_poster(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }

        Storage::fake('public');

        $poster = UploadedFile::fake()->image('tall.jpg', 400, 800);
        $stored = $poster->store('users/1/events/test/poster', 'public');

        $service = app(EventPosterCardService::class);
        $cardPath = $service->generatePortraitCard($stored);

        $this->assertNotNull($cardPath);
        Storage::disk('public')->assertExists($cardPath);

        $info = getimagesize(Storage::disk('public')->path($cardPath));
        $this->assertNotFalse($info);
        $this->assertSame(600, $info[0]);
        $this->assertSame(900, $info[1]);
    }

    public function test_delete_portrait_card_removes_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('users/1/events/test/poster/foo_card.jpg', 'fake');

        app(EventPosterCardService::class)->deletePortraitCard('users/1/events/test/poster/foo_card.jpg');

        Storage::disk('public')->assertMissing('users/1/events/test/poster/foo_card.jpg');
    }
}
