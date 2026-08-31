<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Rating;
use App\Models\Venue;
use App\Models\YoutubeVideo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VenueMergeService
{
    /** Flag manual review when dupes with events are farther apart than this (metres). */
    public const REVIEW_DISTANCE_METERS = 2000;

    public function __construct(
        private readonly DuplicateNameService $duplicateNameService,
    ) {}

    /**
     * Build merge plans for every normalized-name duplicate group.
     *
     * @return list<array{
     *     normalized_name: string,
     *     keeper_id: int,
     *     duplicate_ids: list<int>,
     *     events_to_move: int,
     *     needs_review: bool,
     *     review_reason: string|null,
     *     venues: list<array<string, mixed>>
     * }>
     */
    public function planMerges(): array
    {
        $groups = $this->duplicateNameService->findDuplicatesForEntity('venues');
        $plans = [];

        foreach ($groups as $group) {
            $venues = Venue::query()
                ->whereIn('id', collect($group['items'])->pluck('id'))
                ->withCount('events')
                ->get();

            if ($venues->count() < 2) {
                continue;
            }

            $keeper = $this->pickKeeper($venues);
            $duplicates = $venues->where('id', '!=', $keeper->id)->values();
            [$needsReview, $reason] = $this->reviewStatus($venues, $keeper);

            $plans[] = [
                'normalized_name' => $group['normalized_name'],
                'keeper_id' => $keeper->id,
                'duplicate_ids' => $duplicates->pluck('id')->all(),
                'events_to_move' => $duplicates->sum('events_count'),
                'needs_review' => $needsReview,
                'review_reason' => $reason,
                'venues' => $venues->map(fn (Venue $v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'city' => $v->city,
                    'events' => $v->events_count,
                    'coords' => $v->latitude && $v->longitude
                        ? round((float) $v->latitude, 5).','.round((float) $v->longitude, 5)
                        : null,
                    'keeper' => $v->id === $keeper->id,
                ])->values()->all(),
            ];
        }

        return $plans;
    }

    /**
     * Merge duplicate venues into the keeper inside a transaction.
     *
     * @param  list<int>  $duplicateIds
     * @return array{events_moved: int, duplicates_deleted: int, fields_copied: list<string>}
     */
    public function mergeInto(int $keeperId, array $duplicateIds): array
    {
        $keeper = Venue::query()->findOrFail($keeperId);
        $duplicateIds = array_values(array_filter(
            $duplicateIds,
            fn (int $id) => $id !== $keeperId
        ));

        if ($duplicateIds === []) {
            return ['events_moved' => 0, 'duplicates_deleted' => 0, 'fields_copied' => []];
        }

        $duplicates = Venue::query()->whereIn('id', $duplicateIds)->get();
        $eventsMoved = 0;
        $fieldsCopied = [];
        $deleted = 0;

        DB::transaction(function () use ($keeper, $duplicates, &$eventsMoved, &$fieldsCopied, &$deleted) {
            foreach ($duplicates as $duplicate) {
                $fieldsCopied = array_values(array_unique([
                    ...$fieldsCopied,
                    ...$this->copyMissingFields($keeper, $duplicate),
                ]));

                $eventsMoved += Event::query()
                    ->where('venue_id', $duplicate->id)
                    ->update(['venue_id' => $keeper->id]);

                $this->reassignPivotRows('user_venue_favorites', $keeper->id, $duplicate->id);
                $this->reassignPivotRows('venue_owners', $keeper->id, $duplicate->id);
                $this->reassignVenueOwnerRequests($keeper->id, $duplicate->id);

                $this->reassignMorphRows(Rating::class, 'rateable', $keeper->id, $duplicate->id);
                $this->reassignMorphRows(YoutubeVideo::class, 'videoable', $keeper->id, $duplicate->id);

                $duplicate->delete();
                $deleted++;
            }

            $keeper->save();
        });

        return [
            'events_moved' => $eventsMoved,
            'duplicates_deleted' => $deleted,
            'fields_copied' => $fieldsCopied,
        ];
    }

    public function pickKeeper(Collection $venues): Venue
    {
        return $venues->sort(function (Venue $a, Venue $b) {
            $eventCmp = ($b->events_count ?? $b->events()->count())
                <=> ($a->events_count ?? $a->events()->count());
            if ($eventCmp !== 0) {
                return $eventCmp;
            }

            $score = fn (Venue $v) => [
                ! empty($v->main_picture) ? 1 : 0,
                filled($v->city) ? 1 : 0,
                ($v->latitude && $v->longitude) ? 1 : 0,
                -$v->id,
            ];

            return $score($b) <=> $score($a);
        })->first();
    }

    /**
     * @return array{0: bool, 1: string|null}
     */
    public function reviewStatus(Collection $venues, Venue $keeper): array
    {
        $withEvents = $venues->filter(fn (Venue $v) => ($v->events_count ?? 0) > 0);
        if ($withEvents->count() < 2) {
            return [false, null];
        }

        $counts = $withEvents
            ->map(fn (Venue $v) => $v->events_count ?? 0)
            ->sortDesc()
            ->values();

        $top = (int) ($counts[0] ?? 0);
        $second = (int) ($counts[1] ?? 0);

        if ($second === 0 || $top >= ($second * 2)) {
            return [false, null];
        }

        $maxDistance = $this->maxDistanceMetres($withEvents);
        if ($maxDistance !== null && $maxDistance > self::REVIEW_DISTANCE_METERS) {
            return [true, sprintf(
                'Multiple venues with similar event counts (%d vs %d) and %.0fm apart',
                $top,
                $second,
                $maxDistance
            )];
        }

        $cities = $withEvents
            ->pluck('city')
            ->filter(fn ($city) => filled($city))
            ->map(fn ($city) => Str::lower(trim((string) $city)))
            ->unique()
            ->values();

        if ($cities->count() > 1 && $maxDistance !== null && $maxDistance > 800) {
            return [true, 'Same name with events in different cities ('.$cities->implode(' vs ').')'];
        }

        return [false, null];
    }

    /**
     * @return list<string>
     */
    private function copyMissingFields(Venue $keeper, Venue $duplicate): array
    {
        $copied = [];
        $fields = [
            'city',
            'address',
            'latitude',
            'longitude',
            'google_place_id',
            'main_picture',
            'description',
            'phone_number',
            'website',
            'capacity',
        ];

        foreach ($fields as $field) {
            if (filled($keeper->{$field})) {
                continue;
            }
            if (! filled($duplicate->{$field})) {
                continue;
            }

            $keeper->{$field} = $duplicate->{$field};
            $copied[] = $field;
        }

        return $copied;
    }

    private function reassignPivotRows(string $table, int $keeperId, int $duplicateId): void
    {
        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return;
        }

        $rows = DB::table($table)->where('venue_id', $duplicateId)->get();

        foreach ($rows as $row) {
            $exists = DB::table($table)
                ->where('venue_id', $keeperId)
                ->where('user_id', $row->user_id)
                ->exists();

            if ($exists) {
                DB::table($table)
                    ->where('venue_id', $duplicateId)
                    ->where('user_id', $row->user_id)
                    ->delete();
            } else {
                DB::table($table)
                    ->where('venue_id', $duplicateId)
                    ->where('user_id', $row->user_id)
                    ->update(['venue_id' => $keeperId]);
            }
        }
    }

    private function reassignVenueOwnerRequests(int $keeperId, int $duplicateId): void
    {
        if (! DB::getSchemaBuilder()->hasTable('venue_owner_requests')) {
            return;
        }

        $rows = DB::table('venue_owner_requests')->where('venue_id', $duplicateId)->get();

        foreach ($rows as $row) {
            $exists = DB::table('venue_owner_requests')
                ->where('venue_id', $keeperId)
                ->where('requester_user_id', $row->requester_user_id)
                ->exists();

            if ($exists) {
                DB::table('venue_owner_requests')
                    ->where('venue_id', $duplicateId)
                    ->where('requester_user_id', $row->requester_user_id)
                    ->delete();
            } else {
                DB::table('venue_owner_requests')
                    ->where('venue_id', $duplicateId)
                    ->where('requester_user_id', $row->requester_user_id)
                    ->update(['venue_id' => $keeperId]);
            }
        }
    }

    private function reassignMorphRows(string $modelClass, string $prefix, int $keeperId, int $duplicateId): void
    {
        $morphType = $modelClass === Rating::class
            ? Venue::class
            : Venue::class;

        $query = $modelClass::query()
            ->where($prefix.'_type', $morphType)
            ->where($prefix.'_id', $duplicateId);

        foreach ($query->get() as $row) {
            $userColumn = $modelClass === Rating::class ? 'user_id' : null;

            if ($userColumn !== null) {
                $exists = $modelClass::query()
                    ->where($prefix.'_type', $morphType)
                    ->where($prefix.'_id', $keeperId)
                    ->where($userColumn, $row->{$userColumn})
                    ->exists();

                if ($exists) {
                    $row->delete();
                    continue;
                }
            }

            $row->update([
                $prefix.'_id' => $keeperId,
            ]);
        }
    }

    private function maxDistanceMetres(Collection $venues): ?float
    {
        $located = $venues->filter(fn (Venue $v) => $v->latitude && $v->longitude)->values();
        if ($located->count() < 2) {
            return null;
        }

        $max = 0.0;
        for ($i = 0; $i < $located->count(); $i++) {
            for ($j = $i + 1; $j < $located->count(); $j++) {
                $max = max($max, $this->haversineMeters(
                    (float) $located[$i]->latitude,
                    (float) $located[$i]->longitude,
                    (float) $located[$j]->latitude,
                    (float) $located[$j]->longitude,
                ));
            }
        }

        return $max;
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
}
