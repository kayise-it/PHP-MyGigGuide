<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Rating;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RatingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_can_list_reviews(): void
    {
        $event = $this->makeEvent();
        $user = User::factory()->create(['is_active' => true]);
        Rating::query()->create([
            'user_id' => $user->id,
            'rateable_type' => Event::class,
            'rateable_id' => $event->id,
            'rating' => 4,
            'review' => 'Great night out',
        ]);

        $response = $this->getJson("/api/v1/events/{$event->id}/reviews");

        $response->assertOk()
            ->assertJsonPath('data.0.rating', 4)
            ->assertJsonPath('data.0.review', 'Great night out')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_authenticated_user_can_submit_and_update_rating(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['user']);
        $event = $this->makeEvent();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/ratings', [
            'type' => 'events',
            'id' => $event->id,
            'rating' => 5,
            'review' => 'Loved it',
        ]);

        $create->assertOk()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.review', 'Loved it');

        $update = $this->postJson('/api/v1/ratings', [
            'type' => 'events',
            'id' => $event->id,
            'rating' => 3,
            'review' => 'It was okay',
        ]);

        $update->assertOk()->assertJsonPath('data.rating', 3);
        $this->assertEquals(1, Rating::query()->count());
    }

    public function test_event_show_includes_rating_summary_and_user_rating_when_authenticated(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['user']);
        $event = $this->makeEvent();

        Rating::query()->create([
            'user_id' => $user->id,
            'rateable_type' => Event::class,
            'rateable_id' => $event->id,
            'rating' => 4,
            'review' => 'Solid gig',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertOk()
            ->assertJsonPath('data.rating_summary.average', 4)
            ->assertJsonPath('data.rating_summary.count', 1)
            ->assertJsonPath('data.rating_summary.user.rating', 4);
    }

    private function makeEvent(): Event
    {
        $owner = User::factory()->create(['is_active' => true]);
        $venue = Venue::query()->create([
            'name' => 'Rating Test Venue '.uniqid(),
            'contact_email' => 'venue_'.uniqid().'@example.com',
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
        ]);

        return Event::query()->create([
            'name' => 'Rating Test Event '.uniqid(),
            'date' => now()->addDay(),
            'time' => '20:00',
            'venue_id' => $venue->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);
    }
}
