<?php

namespace App\Console\Commands;

use App\Services\Quicket\QuicketImportService;
use Illuminate\Console\Command;

class QuicketPullCommand extends Command
{
    protected $signature = 'quicket:pull
                            {--page=1 : First Quicket results page}
                            {--max-pages=1 : How many pages to walk from --page}
                            {--page-size= : Override QUICKET_PAGE_SIZE}
                            {--categories= : Override QUICKET_CATEGORIES (comma-separated Quicket IDs)}
                            {--apply : Create venues/events (default is dry-run)}
                            {--all-provinces : Ignore QUICKET_PROVINCES allowlist}
                            {--sleep=1 : Seconds to pause between pages}';

    protected $description = 'Pull Quicket public events (dry-run by default; use --apply to write)';

    public function handle(QuicketImportService $importer): int
    {
        $startPage = max(1, (int) $this->option('page'));
        $maxPages = max(1, (int) $this->option('max-pages'));
        $sleep = max(0, (int) $this->option('sleep'));
        $pageSize = $this->option('page-size');
        $apply = (bool) $this->option('apply');
        $categoriesOpt = $this->option('categories');
        $categoryIds = $categoriesOpt !== null && $categoriesOpt !== ''
            ? $importer->normalizeQuicketCategoryIds($categoriesOpt)
            : $importer->normalizeQuicketCategoryIds(config('quicket.categories', [1]));

        $this->info($apply
            ? 'Quicket pull — APPLY (will write to DB)'
            : 'Quicket pull — dry-run (no DB writes)');
        $this->line('Quicket categories: '.implode(',', $categoryIds));
        $this->line("Pages {$startPage} … ".($startPage + $maxPages - 1)." (stop early if API has fewer)");

        $totals = [
            'fetched' => 0,
            'kept' => 0,
            'skipped_province' => 0,
            'skipped_past' => 0,
            'duplicates' => 0,
            'created' => 0,
            'would_create' => 0,
            'venues_matched' => 0,
            'venues_created' => 0,
        ];

        $apiPages = null;

        for ($i = 0; $i < $maxPages; $i++) {
            $page = $startPage + $i;
            if ($apiPages !== null && $page > $apiPages) {
                break;
            }

            $options = [
                'page' => $page,
                'apply' => $apply,
                'categories' => $categoryIds,
            ];
            if ($pageSize !== null && $pageSize !== '') {
                $options['page_size'] = (int) $pageSize;
            }
            if ($this->option('all-provinces')) {
                $options['provinces'] = [];
            }

            $this->newLine();
            $this->info('— Page '.$page.($apiPages ? " / {$apiPages}" : ''));

            try {
                $stats = $importer->pullPage($options);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            $apiPages = (int) $stats['pages'];
            foreach (array_keys($totals) as $key) {
                $totals[$key] += (int) ($stats[$key] ?? 0);
            }

            $this->table(
                ['Metric', 'Value'],
                [
                    ['API page', $stats['page'].' / '.$stats['pages']],
                    ['Fetched', $stats['fetched']],
                    ['Kept', $stats['kept']],
                    ['Skipped province', $stats['skipped_province']],
                    ['Skipped past / bad', $stats['skipped_past']],
                    ['Duplicates', $stats['duplicates']],
                    [$apply ? 'Created' : 'Would create', $apply ? $stats['created'] : $stats['would_create']],
                    ['Venues matched', $stats['venues_matched']],
                    ['Venues created', $stats['venues_created']],
                ]
            );

            if ($sleep > 0 && $i < $maxPages - 1 && ($apiPages === null || $page < $apiPages)) {
                sleep($sleep);
            }
        }

        $this->newLine();
        $this->info('Totals');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Fetched', $totals['fetched']],
                ['Kept', $totals['kept']],
                ['Skipped province', $totals['skipped_province']],
                ['Skipped past / bad', $totals['skipped_past']],
                ['Duplicates', $totals['duplicates']],
                [$apply ? 'Created' : 'Would create', $apply ? $totals['created'] : $totals['would_create']],
                ['Venues matched', $totals['venues_matched']],
                ['Venues created', $totals['venues_created']],
            ]
        );

        if (! $apply) {
            $this->newLine();
            $this->comment('Dry-run only. Seed example: php artisan quicket:pull --apply --page=1 --max-pages=42 --sleep=2');
        }

        return self::SUCCESS;
    }
}
