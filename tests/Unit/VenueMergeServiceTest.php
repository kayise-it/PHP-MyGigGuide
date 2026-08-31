<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use App\Services\DuplicateNameService;
use App\Services\VenueMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueMergeServiceTest extends TestCase
{
    use RefreshDatabase;

    private VenueMergeService $service;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VenueMergeService(new DuplicateNameService);
        $this->owner = User::factory()->create();
    }

    private function makeEvent(int $venueId, string $name): Event
    {
        return Event::create([
            'name' => $name,
            'date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'time' => '20:00:00',
            'venue_id' => $venueId,
            'owner_id' => $this->owner->id,
            'owner_type' => User::class,
            'status' => 'upcoming',
        ]);
    }

    public function test_pick_keeper_prefers_more_events(): void
    {
        $legacy = Venue::create([
            'name' => 'Woodstock Brewery',
            'contact_email' => 'legacy@example.local',
        ]);
        $quicket = Venue::create([
            'name' => 'Woodstock Brewery',
            'city' => 'Cape Town',
            'contact_email' => 'quicket@example.local',
        ]);

        $this->makeEvent($legacy->id, 'Gig A');
        $this->makeEvent($legacy->id, 'Gig B');
        $this->makeEvent($legacy->id, 'Gig C');
        $this->makeEvent($quicket->id, 'Gig D');

        $venues = Venue::query()->whereIn('id', [$legacy->id, $quicket->id])->withCount('events')->get();
        $keeper = $this->service->pickKeeper($venues);

        $this->assertSame($legacy->id, $keeper->id);
    }

    public function test_dominant_event_count_skips_review(): void
    {
        $keeper = Venue::create([
            'name' => 'GROUND The Venue',
            'city' => 'Krugersdorp',
            'latitude' => -26.00834230,
            'longitude' => 27.83069880,
            'contact_email' => 'ground-a@example.local',
        ]);
        $dupe = Venue::create([
            'name' => 'GROUND The Venue',
            'city' => 'Muldersdrift',
            'latitude' => -25.99660300,
            'longitude' => 27.81660200,
            'contact_email' => 'ground-b@example.local',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->makeEvent($keeper->id, "Ground Event {$i}");
        }
        $this->makeEvent($dupe->id, 'Ground Event dupe');

        $venues = Venue::query()->whereIn('id', [$keeper->id, $dupe->id])->withCount('events')->get();
        [$needsReview] = $this->service->reviewStatus($venues, $keeper);

        $this->assertFalse($needsReview);
    }

    public function test_merge_moves_events_and_copies_city(): void
    {
        $keeper = Venue::create([
            'name' => 'Woodstock Brewery',
            'latitude' => -33.9,
            'longitude' => 18.4,
            'contact_email' => 'keeper@example.local',
        ]);
        $dupe = Venue::create([
            'name' => 'Woodstock Brewery',
            'city' => 'Cape Town',
            'latitude' => -33.9001,
            'longitude' => 18.4001,
            'contact_email' => 'dupe@example.local',
        ]);

        $this->makeEvent($keeper->id, 'Keeper 1');
        $this->makeEvent($keeper->id, 'Keeper 2');
        $this->makeEvent($dupe->id, 'Dupe 1');

        $result = $this->service->mergeInto($keeper->id, [$dupe->id]);

        $this->assertSame(1, $result['events_moved']);
        $this->assertSame(1, $result['duplicates_deleted']);
        $this->assertContains('city', $result['fields_copied']);

        $keeper->refresh();
        $this->assertSame('Cape Town', $keeper->city);
        $this->assertSame(3, Event::query()->where('venue_id', $keeper->id)->count());
        $this->assertNull(Venue::query()->find($dupe->id));
    }
}
