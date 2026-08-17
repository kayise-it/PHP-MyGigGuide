<?php

namespace Tests\Unit;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_taken_place_uses_date_not_status(): void
    {
        $past = Event::query()->make([
            'date' => now()->subDay()->toDateString(),
            'status' => 'upcoming',
        ]);

        $future = Event::query()->make([
            'date' => now()->addDay()->toDateString(),
            'status' => 'upcoming',
        ]);

        $this->assertTrue($past->hasTakenPlace());
        $this->assertFalse($future->hasTakenPlace());
    }

    public function test_same_day_event_is_past_only_after_start_time(): void
    {
        $now = now()->setTime(20, 0);

        $earlierToday = Event::query()->make([
            'date' => $now->toDateString(),
            'time' => '18:00',
            'status' => 'upcoming',
        ]);

        $laterToday = Event::query()->make([
            'date' => $now->toDateString(),
            'time' => '22:00',
            'status' => 'upcoming',
        ]);

        $this->assertTrue($earlierToday->hasTakenPlace($now));
        $this->assertFalse($laterToday->hasTakenPlace($now));
    }
}
