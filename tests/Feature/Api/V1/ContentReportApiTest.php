<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContentReport;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContentReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_submit_report(): void
    {
        $event = $this->makeEvent();

        $response = $this->postJson('/api/v1/reports', [
            'type' => 'events',
            'id' => $event->id,
            'category' => 'wrong_date_time',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_submit_report(): void
    {
        Mail::fake();

        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['user']);
        $event = $this->makeEvent();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reports', [
            'type' => 'events',
            'id' => $event->id,
            'category' => 'wrong_date_time',
            'message' => 'Starts at 9pm not 8pm',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.category', 'wrong_date_time')
            ->assertJsonPath('data.status', 'new');

        $this->assertDatabaseCount('content_reports', 1);
        Mail::assertSent(\App\Mail\ContentReportMail::class);
    }

    public function test_duplicate_open_report_updates_message_instead_of_new_row(): void
    {
        Mail::fake();

        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['user']);
        $event = $this->makeEvent();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/reports', [
            'type' => 'events',
            'id' => $event->id,
            'category' => 'wrong_date_time',
            'message' => 'First note',
        ])->assertCreated();

        $this->postJson('/api/v1/reports', [
            'type' => 'events',
            'id' => $event->id,
            'category' => 'wrong_date_time',
            'message' => 'Updated note',
        ])->assertCreated();

        $this->assertDatabaseCount('content_reports', 1);
        $this->assertDatabaseHas('content_reports', [
            'message' => 'Updated note',
        ]);
        Mail::assertSent(\App\Mail\ContentReportMail::class, 1);
    }

    public function test_invalid_category_is_rejected(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['user']);
        $event = $this->makeEvent();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reports', [
            'type' => 'events',
            'id' => $event->id,
            'category' => 'not_a_real_category',
        ]);

        $response->assertUnprocessable();
    }

    public function test_daily_limit_is_enforced(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            $event = $this->makeEvent();
            $this->postJson('/api/v1/reports', [
                'type' => 'events',
                'id' => $event->id,
                'category' => 'other',
                'message' => "Report $i",
            ])->assertCreated();
        }

        $extraEvent = $this->makeEvent();
        $blocked = $this->postJson('/api/v1/reports', [
            'type' => 'events',
            'id' => $extraEvent->id,
            'category' => 'other',
        ]);

        $blocked->assertUnprocessable();
        $this->assertEquals(5, ContentReport::query()->count());
    }

    private function makeEvent(): Event
    {
        $owner = User::factory()->create(['is_active' => true]);
        $venue = Venue::query()->create([
            'name' => 'Report Test Venue '.uniqid(),
            'contact_email' => 'venue_'.uniqid().'@example.com',
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
        ]);

        return Event::query()->create([
            'name' => 'Report Test Event '.uniqid(),
            'date' => now()->addDay(),
            'time' => '20:00',
            'venue_id' => $venue->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);
    }
}
