<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventCheckInApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    private function makeEvent(User $owner, array $attrs = []): Event
    {
        return Event::query()->create(array_merge([
            'name' => 'Friday Gig',
            'date' => now(),
            'time' => '20:00',
            'status' => 'upcoming',
            'owner_type' => 'user',
            'owner_id' => $owner->id,
        ], $attrs));
    }

    public function test_fan_can_check_in_and_see_board(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $artist = Artist::query()->create(['stage_name' => 'Danger Dave']);
        $event = $this->makeEvent($owner);
        $event->artists()->attach($artist->id);

        Sanctum::actingAs($fan);

        $this->postJson("/api/v1/events/{$event->id}/check-in")
            ->assertCreated()
            ->assertJsonPath('data.checked_in', true)
            ->assertJsonPath('data.check_in_count', 1)
            ->assertJsonPath('data.artists.0.stage_name', 'Danger Dave');

        $this->getJson("/api/v1/events/{$event->id}/board")
            ->assertOk()
            ->assertJsonPath('data.check_in_count', 1)
            ->assertJsonPath('data.checked_in', true);

        $this->postJson("/api/v1/events/{$event->id}/check-in")
            ->assertCreated()
            ->assertJsonPath('data.check_in_count', 1);

        Sanctum::actingAs($fan);
        $this->deleteJson("/api/v1/events/{$event->id}/check-in")
            ->assertOk()
            ->assertJsonPath('data.checked_in', false)
            ->assertJsonPath('data.check_in_count', 0);
    }

    public function test_venue_tonight_lists_today_events(): void
    {
        $owner = User::factory()->create();
        $venue = Venue::query()->create([
            'name' => 'The Local',
            'contact_email' => 'pub@example.com',
            'owner_type' => 'user',
            'owner_id' => $owner->id,
        ]);

        $today = $this->makeEvent($owner, [
            'name' => 'Tonight',
            'venue_id' => $venue->id,
            'date' => now(),
        ]);
        $tomorrow = $this->makeEvent($owner, [
            'name' => 'Tomorrow',
            'venue_id' => $venue->id,
            'date' => now()->addDay(),
        ]);

        $this->getJson("/api/v1/venues/{$venue->id}/tonight")
            ->assertOk()
            ->assertJsonPath('data.event_count', 1)
            ->assertJsonPath('data.events.0.event.name', 'Tonight');
    }

    public function test_check_in_rejected_before_event_day(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $event = $this->makeEvent($owner, ['date' => now()->addDays(3)]);

        Sanctum::actingAs($fan);

        $this->postJson("/api/v1/events/{$event->id}/check-in")
            ->assertStatus(422);
    }
}
