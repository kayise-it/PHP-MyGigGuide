<?php

namespace App\Console\Commands;

use App\Services\Fixr\FixrApiClient;
use Illuminate\Console\Command;

class FixrPullCommand extends Command
{
    protected $signature = 'fixr:pull {--limit=20 : Max events to preview}';

    protected $description = 'Dry-run FIXR organiser event feed (requires FIXR_API_TOKEN + FIXR_EVENTS_URL)';

    public function handle(FixrApiClient $client): int
    {
        $this->info('FIXR pull — dry-run preview only (no DB writes yet)');

        try {
            $events = $client->listEvents();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->newLine();
            $this->comment('FIXR’s documented API covers *your* organiser account only.');
            $this->comment('Request a token: https://docs.fixr.co/api/ — then set FIXR_API_TOKEN + FIXR_EVENTS_URL.');

            return self::FAILURE;
        }

        $limit = max(1, (int) $this->option('limit'));
        $this->info('Fetched '.count($events).' event object(s); showing up to '.$limit);

        $rows = [];
        foreach (array_slice($events, 0, $limit) as $i => $event) {
            if (! is_array($event)) {
                continue;
            }
            $rows[] = [
                $i + 1,
                mb_substr((string) ($event['name'] ?? $event['title'] ?? $event['id'] ?? '—'), 0, 40),
                mb_substr((string) ($event['start'] ?? $event['starts_at'] ?? $event['date'] ?? ''), 0, 22),
                mb_substr((string) ($event['url'] ?? $event['link'] ?? ''), 0, 40),
            ];
        }

        if ($rows === []) {
            $this->warn('No listable events (empty feed or unexpected JSON shape). Dump first keys:');
            $first = $events[0] ?? null;
            if (is_array($first)) {
                $this->line(implode(', ', array_slice(array_keys($first), 0, 20)));
            }

            return self::SUCCESS;
        }

        $this->table(['#', 'Name/title', 'When', 'URL'], $rows);

        return self::SUCCESS;
    }
}
