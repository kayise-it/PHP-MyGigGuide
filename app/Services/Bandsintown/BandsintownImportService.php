<?php

namespace App\Services\Bandsintown;

use App\Helpers\NameNormalizer;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use App\Services\EventDuplicateService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class BandsintownImportService
{
    private const VENUE_PROXIMITY_METERS = 400;

    public function __construct(
        private readonly BandsintownApiClient $client,
        private readonly EventDuplicateService $duplicateService,
    ) {}

    /**
     * @param  array{artist: string, apply?: bool, countries?: list<string>|null, cities?: list<string>|null}  $options
     * @return array<string, mixed>
     */
    public function pullArtist(array $options): array
    {
        $artist = trim((string) ($options['artist'] ?? ''));
        $apply = (bool) ($options['apply'] ?? false);

        $rawEvents = $this->client->artistEvents($artist);

        $countries = $options['countries'] ?? config('bandsintown.country_allowlist', ['ZA']);
        if (! is_array($countries)) {
            $countries = [];
        }
        $countries = array_values(array_filter(array_map(
            fn ($c) => strtoupper(trim((string) $c)),
            $countries
        )));

        $cities = $options['cities'] ?? config('bandsintown.city_allowlist', []);
        if (! is_array($cities)) {
            $cities = [];
        }
        $citiesLower = array_values(array_filter(array_map(
            fn ($c) => Str::lower(trim((string) $c)),
            $cities
        )));

        $owner = $apply ? $this->resolveOwnerUser() : null;

        $stats = [
            'artist' => $artist,
            'fetched' => count($rawEvents),
            'kept' => 0,
            'skipped_country' => 0,
            'skipped_city' => 0,
            'skipped_past' => 0,
            'would_create' => 0,
            'created' => 0,
            'duplicates' => 0,
            'venues_matched' => 0,
            'venues_created' => 0,
            'rows' => [],
        ];

        foreach ($rawEvents as $raw) {
            $mapped = $this->mapEvent($raw, $artist);
            if ($mapped === null) {
                $stats['skipped_past']++;
                continue;
            }

            if ($countries !== [] && ! in_array(strtoupper($mapped['country']), $countries, true)) {
                $stats['skipped_country']++;
                $stats['rows'][] = [
                    'action' => 'skip_country',
                    'name' => $mapped['name'],
                    'country' => $mapped['country'],
                    'city' => $mapped['city'],
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            if ($citiesLower !== [] && ! $this->cityAllowed($mapped['city'], $citiesLower)) {
                $stats['skipped_city']++;
                $stats['rows'][] = [
                    'action' => 'skip_city',
                    'name' => $mapped['name'],
                    'city' => $mapped['city'],
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            $stats['kept']++;

            $duplicate = null;
            if ($mapped['ticket_url'] !== '') {
                $duplicate = $this->duplicateService->findDuplicate([
                    'name' => $mapped['name'],
                    'date' => $mapped['date'],
                    'time' => $mapped['time'],
                    'venue_id' => 0,
                    'ticket_url' => $mapped['ticket_url'],
                ]);
            }

            if ($duplicate !== null) {
                $stats['duplicates']++;
                $stats['rows'][] = [
                    'action' => 'duplicate',
                    'name' => $mapped['name'],
                    'date' => $mapped['date'],
                    'time' => $mapped['time'],
                    'existing_event_id' => $duplicate->id,
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            if (! $apply) {
                $stats['would_create']++;
                $stats['rows'][] = [
                    'action' => 'would_create',
                    'name' => $mapped['name'],
                    'date' => $mapped['date'],
                    'time' => $mapped['time'],
                    'venue' => $mapped['venue_name'],
                    'city' => $mapped['city'],
                    'country' => $mapped['country'],
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            $venueResult = $this->resolveVenue($mapped);
            if ($venueResult['created']) {
                $stats['venues_created']++;
            } else {
                $stats['venues_matched']++;
            }

            $duplicateAfterVenue = $this->duplicateService->findDuplicate([
                'name' => $mapped['name'],
                'date' => $mapped['date'],
                'time' => $mapped['time'],
                'venue_id' => $venueResult['venue']->id,
                'ticket_url' => $mapped['ticket_url'],
            ]);
            if ($duplicateAfterVenue !== null) {
                $stats['duplicates']++;
                $stats['rows'][] = [
                    'action' => 'duplicate',
                    'name' => $mapped['name'],
                    'existing_event_id' => $duplicateAfterVenue->id,
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            $event = Event::create([
                'name' => $mapped['name'],
                'description' => $mapped['description'],
                'date' => $mapped['date'],
                'time' => $mapped['time'],
                'price' => 0,
                'ticket_url' => $mapped['ticket_url'] !== '' ? $mapped['ticket_url'] : null,
                'status' => 'upcoming',
                'venue_id' => $venueResult['venue']->id,
                'owner_id' => $owner->id,
                'owner_type' => 'user',
            ]);

            $stats['created']++;
            $stats['rows'][] = [
                'action' => 'created',
                'event_id' => $event->id,
                'name' => $mapped['name'],
                'date' => $mapped['date'],
                'time' => $mapped['time'],
                'venue' => $mapped['venue_name'],
                'venue_match' => $venueResult['match_method'],
                'ticket_url' => $mapped['ticket_url'],
            ];
        }

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>|null
     */
    public function mapEvent(array $raw, string $fallbackArtist): ?array
    {
        $datetime = (string) ($raw['datetime'] ?? $raw['starts_at'] ?? '');
        if ($datetime === '') {
            return null;
        }

        try {
            $start = Carbon::parse($datetime, 'Africa/Johannesburg');
        } catch (\Throwable) {
            return null;
        }

        if ($start->lt(now('Africa/Johannesburg')->startOfDay())) {
            return null;
        }

        $venue = is_array($raw['venue'] ?? null) ? $raw['venue'] : [];
        $title = trim((string) ($raw['title'] ?? ''));
        $lineup = is_array($raw['lineup'] ?? null) ? $raw['lineup'] : [];
        $headliner = is_string($lineup[0] ?? null) ? trim($lineup[0]) : $fallbackArtist;
        $name = $title !== '' ? $title : ($headliner !== '' ? $headliner : 'Untitled gig');

        $ticketUrl = $this->ticketUrlFromOffers($raw);
        if ($ticketUrl === '') {
            $ticketUrl = trim((string) ($raw['url'] ?? ''));
        }

        $description = trim(html_entity_decode(strip_tags((string) ($raw['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (mb_strlen($description) > 5000) {
            $description = mb_substr($description, 0, 5000).'…';
        }

        $lat = isset($venue['latitude']) && is_numeric($venue['latitude']) ? (float) $venue['latitude'] : null;
        $lng = isset($venue['longitude']) && is_numeric($venue['longitude']) ? (float) $venue['longitude'] : null;

        return [
            'bandsintown_id' => $raw['id'] ?? null,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'date' => $start->toDateString(),
            'time' => $start->format('H:i'),
            'ticket_url' => $ticketUrl,
            'venue_name' => trim((string) ($venue['name'] ?? '')) ?: 'TBA',
            'venue_address' => trim((string) ($venue['street_address'] ?? $venue['location'] ?? '')),
            'city' => trim((string) ($venue['city'] ?? '')),
            'region' => trim((string) ($venue['region'] ?? '')),
            'country' => strtoupper(trim((string) ($venue['country'] ?? ''))),
            'latitude' => $lat,
            'longitude' => $lng,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function ticketUrlFromOffers(array $raw): string
    {
        $offers = $raw['offers'] ?? [];
        if (! is_array($offers)) {
            return '';
        }

        foreach ($offers as $offer) {
            if (! is_array($offer)) {
                continue;
            }
            $url = trim((string) ($offer['url'] ?? ''));
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }

    /**
     * @param  list<string>  $allowlistLower
     */
    private function cityAllowed(string $city, array $allowlistLower): bool
    {
        $c = Str::lower(trim($city));
        if ($c === '') {
            return false;
        }

        foreach ($allowlistLower as $allowed) {
            if ($allowed !== '' && (Str::contains($c, $allowed) || Str::contains($allowed, $c))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return array{venue: Venue, created: bool, match_method: string}
     */
    private function resolveVenue(array $mapped): array
    {
        $name = (string) $mapped['venue_name'];
        $lat = $mapped['latitude'];
        $lng = $mapped['longitude'];

        if (is_float($lat) && is_float($lng)) {
            $nearby = $this->findNearbyByName($name, $lat, $lng);
            if ($nearby !== null) {
                return ['venue' => $nearby, 'created' => false, 'match_method' => 'proximity'];
            }
        }

        $byNameCity = $this->findByNameAndCity($name, (string) $mapped['city']);
        if ($byNameCity !== null) {
            return ['venue' => $byNameCity, 'created' => false, 'match_method' => 'name_city'];
        }

        $venue = Venue::create([
            'name' => $name,
            'address' => $mapped['venue_address'] !== '' ? $mapped['venue_address'] : null,
            'city' => $mapped['city'] !== '' ? $mapped['city'] : null,
            'latitude' => $lat,
            'longitude' => $lng,
            'contact_email' => 'bandsintown-import+'.Str::lower(Str::random(8)).'@example.local',
            'user_id' => null,
            'owner_id' => null,
            'owner_type' => null,
        ]);

        return ['venue' => $venue, 'created' => true, 'match_method' => 'created'];
    }

    private function findNearbyByName(string $name, float $lat, float $lng): ?Venue
    {
        $candidates = Venue::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$lat - 0.05, $lat + 0.05])
            ->whereBetween('longitude', [$lng - 0.05, $lng + 0.05])
            ->limit(80)
            ->get();

        foreach ($candidates as $venue) {
            if (! $this->namesLikelySame($name, (string) $venue->name)) {
                continue;
            }
            $distance = $this->haversineMeters($lat, $lng, (float) $venue->latitude, (float) $venue->longitude);
            if ($distance <= self::VENUE_PROXIMITY_METERS) {
                return $venue;
            }
        }

        return null;
    }

    private function findByNameAndCity(string $name, string $city): ?Venue
    {
        $query = Venue::query()->orderBy('id');
        if ($city !== '') {
            $query->where('city', 'like', '%'.$city.'%');
        }

        foreach ($query->limit(100)->get() as $venue) {
            if ($this->namesLikelySame($name, (string) $venue->name)) {
                return $venue;
            }
        }

        return null;
    }

    private function namesLikelySame(string $a, string $b): bool
    {
        if (NameNormalizer::areDuplicates($a, $b)) {
            return true;
        }

        $na = NameNormalizer::normalize($a);
        $nb = NameNormalizer::normalize($b);
        if ($na === '' || $nb === '') {
            return false;
        }

        similar_text($na, $nb, $percent);

        return $percent >= 88.0;
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }

    private function resolveOwnerUser(): User
    {
        $id = config('bandsintown.owner_user_id');
        if (! is_int($id) || $id < 1) {
            throw new RuntimeException(
                'Set BANDSINTOWN_OWNER_USER_ID or QUICKET_OWNER_USER_ID before --apply.'
            );
        }

        $user = User::query()->find($id);
        if ($user === null) {
            throw new RuntimeException("Owner user id {$id} does not exist.");
        }

        return $user;
    }
}
