<?php

namespace App\Console\Commands;

use App\Services\Quicket\QuicketImportService;
use Illuminate\Console\Command;

class QuicketBackfillCategoriesCommand extends Command
{
    protected $signature = 'quicket:backfill-categories
                            {--limit=500 : Max Quicket-linked events to process}
                            {--quicket-category=1 : Quicket API category id (1=Music, 5=Sports, 6=Travel)}
                            {--apply : Attach categories (default dry-run)}';

    protected $description = 'Attach MGG categories (from category_slug_map) to existing Quicket-imported events';

    public function handle(QuicketImportService $importer): int
    {
        $apply = (bool) $this->option('apply');
        $quicketCategoryId = max(1, (int) $this->option('quicket-category'));
        $slugs = $importer->mggSlugsForQuicketCategory($quicketCategoryId);

        $this->info($apply
            ? 'Quicket category backfill — APPLY'
            : 'Quicket category backfill — dry-run');
        $this->line('Quicket category id: '.$quicketCategoryId);
        $this->line('Target slugs: '.implode(', ', $slugs ?: ['(none)']));

        $stats = $importer->backfillCategories([
            'limit' => max(1, (int) $this->option('limit')),
            'apply' => $apply,
            'quicket_category_id' => $quicketCategoryId,
        ]);

        if ($stats['missing_categories'] !== []) {
            $this->warn(
                'Missing categories in DB (create in admin, slug must match): '
                .implode(', ', $stats['missing_categories'])
            );
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Candidates', $stats['candidates']],
                ['Already tagged', $stats['already_tagged']],
                ['Would update', $stats['would_update']],
                ['Updated', $stats['updated']],
            ]
        );

        $preview = array_slice($stats['rows'], 0, 15);
        if ($preview !== []) {
            $this->table(
                ['Action', 'Event', 'Name', 'Adding'],
                array_map(fn (array $row) => [
                    $row['action'] ?? '',
                    $row['event_id'] ?? '',
                    mb_substr((string) ($row['name'] ?? ''), 0, 28),
                    $row['adding'] ?? '',
                ], $preview)
            );
        }

        if (! $apply) {
            $this->comment(
                'Dry-run only. When ready: php artisan quicket:backfill-categories --quicket-category='
                .$quicketCategoryId.' --apply'
            );
        }

        return self::SUCCESS;
    }
}
