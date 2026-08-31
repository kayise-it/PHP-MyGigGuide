<?php

namespace App\Console\Commands;

use App\Services\VenueMergeService;
use Illuminate\Console\Command;

class MergeVenueDuplicatesCommand extends Command
{
    protected $signature = 'venues:merge-duplicates
                            {--dry-run : Show merge plan without writing}
                            {--apply : Execute merges (skips review-flagged groups unless --include-review)}
                            {--include-review : Apply merges even for review-flagged groups}
                            {--group= : Only process one normalized name (e.g. groundthevenue)}';

    protected $description = 'Merge duplicate venues (same normalized name) — reassign events and delete dupes';

    public function handle(VenueMergeService $mergeService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $apply = (bool) $this->option('apply');
        $includeReview = (bool) $this->option('include-review');
        $groupFilter = $this->option('group');

        if (! $dryRun && ! $apply) {
            $dryRun = true;
            $this->warn('No mode specified — showing dry-run. Pass --apply to execute.');
        }

        if ($apply && $dryRun) {
            $this->error('Use either --dry-run or --apply, not both.');

            return self::FAILURE;
        }

        $plans = $mergeService->planMerges();

        if ($groupFilter !== null && $groupFilter !== '') {
            $needle = strtolower((string) $groupFilter);
            $plans = array_values(array_filter(
                $plans,
                fn (array $plan) => $plan['normalized_name'] === $needle
            ));
        }

        if ($plans === []) {
            $this->info('No duplicate venue groups found.');

            return self::SUCCESS;
        }

        $autoCount = 0;
        $reviewCount = 0;
        $eventsToMove = 0;
        $dupesToDelete = 0;

        foreach ($plans as $plan) {
            if ($plan['needs_review']) {
                $reviewCount++;
            } else {
                $autoCount++;
            }
            $eventsToMove += $plan['events_to_move'];
            $dupesToDelete += count($plan['duplicate_ids']);
        }

        $this->info(sprintf(
            'Found %d duplicate groups (%d auto, %d need review). Would move %d events and delete %d venue rows.',
            count($plans),
            $autoCount,
            $reviewCount,
            $eventsToMove,
            $dupesToDelete
        ));
        $this->newLine();

        $applied = 0;
        $skipped = 0;

        foreach ($plans as $plan) {
            $flag = $plan['needs_review'] ? 'REVIEW' : 'AUTO';
            $this->line("<fg=cyan>[$flag]</> {$plan['normalized_name']}");

            foreach ($plan['venues'] as $venue) {
                $marker = $venue['keeper'] ? '→ keep' : '  merge';
                $this->line(sprintf(
                    '  %s #%d %s | events=%d | city=%s | %s',
                    $marker,
                    $venue['id'],
                    $venue['name'],
                    $venue['events'],
                    $venue['city'] ?: '(empty)',
                    $venue['coords'] ?? 'no coords'
                ));
            }

            if ($plan['needs_review'] && $plan['review_reason']) {
                $this->line('  <fg=yellow>Reason: '.$plan['review_reason'].'</>');
            }

            $shouldApply = $apply && (! $plan['needs_review'] || $includeReview);

            if ($shouldApply) {
                $result = $mergeService->mergeInto($plan['keeper_id'], $plan['duplicate_ids']);
                $this->line(sprintf(
                    '  <fg=green>Applied:</> moved %d events, deleted %d dupes%s',
                    $result['events_moved'],
                    $result['duplicates_deleted'],
                    $result['fields_copied'] !== [] ? ' (copied: '.implode(', ', $result['fields_copied']).')' : ''
                ));
                $applied++;
            } elseif ($apply && $plan['needs_review']) {
                $this->line('  <fg=yellow>Skipped (review) — re-run with --include-review to merge</>');
                $skipped++;
            } else {
                $this->line(sprintf(
                    '  Would merge %s → #%d (%d events)',
                    implode(', ', array_map(fn ($id) => '#'.$id, $plan['duplicate_ids'])),
                    $plan['keeper_id'],
                    $plan['events_to_move']
                ));
            }

            $this->newLine();
        }

        if ($apply) {
            $this->info("Applied {$applied} merges, skipped {$skipped} review groups.");
        } else {
            $this->comment('Dry-run complete. Run with --apply to execute auto merges.');
            if ($reviewCount > 0) {
                $this->comment('Review-flagged groups need --include-review or manual check.');
            }
        }

        return self::SUCCESS;
    }
}
