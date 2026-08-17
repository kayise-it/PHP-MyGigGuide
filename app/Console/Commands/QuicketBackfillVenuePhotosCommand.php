<?php

namespace App\Console\Commands;

use App\Models\Venue;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;

class QuicketBackfillVenuePhotosCommand extends Command
{
    protected $signature = 'quicket:backfill-venue-photos
                            {--limit=20 : Max venues without a main picture}
                            {--apply : Download Google Place photos (default dry-run)}';

    protected $description = 'Attach Google Place photos to Quicket-imported venues missing main_picture';

    public function handle(GooglePlacesService $googlePlaces): int
    {
        $apply = (bool) $this->option('apply');
        $limit = max(1, (int) $this->option('limit'));

        $venues = Venue::query()
            ->where(function ($q) {
                $q->whereNull('main_picture')->orWhere('main_picture', '');
            })
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('contact_email', 'like', 'quicket-import+%@example.local')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $this->info($apply ? 'Venue photo backfill — APPLY' : 'Venue photo backfill — dry-run');
        $this->info('Candidates: '.$venues->count());

        $updated = 0;
        $failed = 0;

        foreach ($venues as $venue) {
            $found = $googlePlaces->findPlaceNear(
                (string) $venue->name,
                $venue->latitude !== null ? (float) $venue->latitude : null,
                $venue->longitude !== null ? (float) $venue->longitude : null
            );

            if ($found === null) {
                $this->line('skip  #'.$venue->id.' '.$venue->name.' (no Google match)');
                $failed++;
                continue;
            }

            if (! $apply) {
                $this->line('would #'.$venue->id.' '.$venue->name.' → '.$found['place_id']);
                continue;
            }

            $photoRef = $found['photo_reference']
                ?: $googlePlaces->firstPhotoReferenceForPlace($found['place_id']);

            $updates = [];
            if (empty($venue->google_place_id)) {
                $updates['google_place_id'] = $found['place_id'];
            }

            if ($photoRef !== null) {
                $path = $googlePlaces->downloadPhoto($photoRef, 'imports/quicket/venues');
                if ($path !== null) {
                    $updates['main_picture'] = $path;
                }
            }

            if ($updates === []) {
                $this->line('fail  #'.$venue->id.' '.$venue->name.' (no photo)');
                $failed++;
                continue;
            }

            $venue->update($updates);
            $updated++;
            $this->line('ok    #'.$venue->id.' '.$venue->name);
        }

        if ($apply) {
            $this->info("Updated {$updated}; failed/skipped {$failed}");
        } else {
            $this->comment('Dry-run only. When ready: php artisan quicket:backfill-venue-photos --apply');
        }

        return self::SUCCESS;
    }
}
