<?php

namespace App\Console\Commands;

use App\Services\Quicket\QuicketImportService;
use Illuminate\Console\Command;

class QuicketBackfillPostersCommand extends Command
{
    protected $signature = 'quicket:backfill-posters
                            {--limit=50 : Max Quicket-linked events to process}
                            {--force : Re-download posters / regenerate cards even if already present}
                            {--cards-only : Only generate poster_card from existing local posters (no Quicket download; skips landscapes)}
                            {--clear-cards : Clear ALL Quicket poster_card values (restore full poster + letterbox)}
                            {--clear-landscape-cards : Clear poster_card only when underlying poster is landscape}
                            {--apply : Write changes (default dry-run)}';

    protected $description = 'Download Quicket posters, regenerate portrait cards, or clear forced Quicket poster_cards';

    public function handle(QuicketImportService $importer): int
    {
        $apply = (bool) $this->option('apply');
        $clearCards = (bool) $this->option('clear-cards');
        $clearLandscape = (bool) $this->option('clear-landscape-cards');
        $cardsOnly = (bool) $this->option('cards-only');

        if ($clearCards && $clearLandscape) {
            $this->error('Use either --clear-cards or --clear-landscape-cards, not both.');

            return self::FAILURE;
        }

        if ($clearCards || $clearLandscape) {
            return $this->runClearCards($importer, $apply, $clearLandscape);
        }

        $options = [
            'limit' => max(1, (int) $this->option('limit')),
            'apply' => $apply,
            'force' => (bool) $this->option('force'),
        ];

        if ($cardsOnly) {
            $this->info($apply
                ? 'Quicket poster_card backfill (portrait only) — APPLY'
                : 'Quicket poster_card backfill (portrait only) — dry-run');

            try {
                $stats = $importer->backfillPosterCards($options);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Candidates', $stats['candidates']],
                    ['Would update', $stats['would_update']],
                    ['Updated', $stats['updated']],
                    ['Skipped landscape', $stats['skipped_landscape']],
                    ['Missing poster file', $stats['missing_file']],
                    ['Failed crop', $stats['failed']],
                ]
            );

            $this->previewRows($stats['rows'] ?? [], 'poster_card', 'poster');

            if (! $apply) {
                $this->comment('Dry-run only. When ready: php artisan quicket:backfill-posters --cards-only --force --apply --limit=500');
                $this->comment('To restore letterbox lists first: php artisan quicket:backfill-posters --clear-cards --apply --limit=500');
            }

            return self::SUCCESS;
        }

        $this->info($apply
            ? 'Quicket poster backfill — APPLY'
            : 'Quicket poster backfill — dry-run');

        try {
            $stats = $importer->backfillPosters($options);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Candidates', $stats['candidates']],
                ['Matched Quicket id', $stats['matched']],
                ['Would update', $stats['would_update']],
                ['Updated', $stats['updated']],
                ['No image on Quicket', $stats['no_image']],
                ['Not found / bad URL', $stats['not_found']],
                ['Failed download', $stats['failed']],
            ]
        );

        $this->previewRows($stats['rows'] ?? [], 'image_url', 'poster');

        if (! $apply) {
            $this->comment('Dry-run only. When ready: php artisan quicket:backfill-posters --force --apply');
            $this->comment('Clear forced Quicket cards: php artisan quicket:backfill-posters --clear-cards --apply --limit=500');
            $this->comment('Portrait cards only: php artisan quicket:backfill-posters --cards-only --force --apply --limit=500');
        }

        return self::SUCCESS;
    }

    private function runClearCards(QuicketImportService $importer, bool $apply, bool $landscapeOnly): int
    {
        $label = $landscapeOnly ? 'landscape poster_card clear' : 'ALL Quicket poster_card clear';
        $this->info($apply
            ? "Quicket {$label} — APPLY"
            : "Quicket {$label} — dry-run");

        try {
            $stats = $importer->clearQuicketPosterCards([
                'limit' => max(1, (int) $this->option('limit')),
                'apply' => $apply,
                'landscape_only' => $landscapeOnly,
            ]);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $rows = [
            ['Candidates', $stats['candidates']],
            ['Would clear', $stats['would_clear']],
            ['Cleared', $stats['cleared']],
        ];
        if ($landscapeOnly) {
            $rows[] = ['Skipped portrait', $stats['skipped_portrait']];
            $rows[] = ['Missing poster (still cleared on apply)', $stats['missing_poster']];
        }

        $this->table(['Metric', 'Value'], $rows);
        $this->previewRows($stats['rows'] ?? [], 'poster_card');

        if (! $apply) {
            $flag = $landscapeOnly ? '--clear-landscape-cards' : '--clear-cards';
            $this->comment("Dry-run only. When ready: php artisan quicket:backfill-posters {$flag} --apply --limit=500");
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function previewRows(array $rows, string ...$pathKeys): void
    {
        $preview = array_slice($rows, 0, 15);
        if ($preview === []) {
            return;
        }

        $this->table(
            ['Action', 'Event', 'Name', 'Path'],
            array_map(function (array $row) use ($pathKeys) {
                $path = '';
                foreach ($pathKeys as $key) {
                    if (! empty($row[$key])) {
                        $path = (string) $row[$key];
                        break;
                    }
                }

                return [
                    $row['action'] ?? '',
                    $row['event_id'] ?? '',
                    mb_substr((string) ($row['name'] ?? ''), 0, 28),
                    mb_substr($path, 0, 42),
                ];
            }, $preview)
        );
    }
}
