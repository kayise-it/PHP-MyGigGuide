<?php

namespace App\Console\Commands;

use App\Services\Bandsintown\BandsintownImportService;
use Illuminate\Console\Command;

class BandsintownPullCommand extends Command
{
    protected $signature = 'bandsintown:pull
                            {artist? : Artist name (or use BANDSINTOWN_ARTISTS)}
                            {--apply : Create venues/events (default dry-run)}
                            {--all-countries : Ignore BANDSINTOWN_COUNTRIES allowlist}';

    protected $description = 'Pull Bandsintown events for an artist (dry-run by default)';

    public function handle(BandsintownImportService $importer): int
    {
        $artist = trim((string) ($this->argument('artist') ?? ''));
        $artists = $artist !== ''
            ? [$artist]
            : (array) config('bandsintown.artists', []);

        if ($artists === []) {
            $this->error('Pass an artist name, or set BANDSINTOWN_ARTISTS in .env (pipe-separated).');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $this->info($apply
            ? 'Bandsintown pull — APPLY'
            : 'Bandsintown pull — dry-run');

        foreach ($artists as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $this->newLine();
            $this->line('Artist: '.$name);

            $options = [
                'artist' => $name,
                'apply' => $apply,
            ];
            if ($this->option('all-countries')) {
                $options['countries'] = [];
            }

            try {
                $stats = $importer->pullArtist($options);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Fetched', $stats['fetched']],
                    ['Kept', $stats['kept']],
                    ['Skipped country', $stats['skipped_country']],
                    ['Skipped city', $stats['skipped_city']],
                    ['Skipped past', $stats['skipped_past']],
                    ['Duplicates', $stats['duplicates']],
                    [$apply ? 'Created' : 'Would create', $apply ? $stats['created'] : $stats['would_create']],
                    ['Venues matched', $stats['venues_matched']],
                    ['Venues created', $stats['venues_created']],
                ]
            );

            $preview = array_slice($stats['rows'], 0, 15);
            if ($preview !== []) {
                $this->table(
                    ['Action', 'Name', 'When', 'Where', 'Ticket'],
                    array_map(function (array $row) {
                        $when = trim(($row['date'] ?? '').' '.($row['time'] ?? ''));
                        $where = trim(($row['venue'] ?? '').' '.($row['city'] ?? $row['country'] ?? ''));

                        return [
                            $row['action'] ?? '',
                            mb_substr((string) ($row['name'] ?? ''), 0, 36),
                            $when,
                            mb_substr($where, 0, 28),
                            mb_substr((string) ($row['ticket_url'] ?? ''), 0, 42),
                        ];
                    }, $preview)
                );
            }
        }

        if (! $apply) {
            $this->newLine();
            $this->comment('Dry-run only. Example: php artisan bandsintown:pull "Artist Name" --apply');
        }

        return self::SUCCESS;
    }
}
