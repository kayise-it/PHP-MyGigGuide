<?php

namespace App\Console\Commands;

use App\Services\YoutubeVideoService;
use Illuminate\Console\Command;

class BackfillYoutubeTitles extends Command
{
    protected $signature = 'youtube:backfill-titles {--limit=100 : Max rows to update this run}';

    protected $description = 'Fetch missing YouTube video titles via oEmbed';

    public function handle(YoutubeVideoService $youtubeVideos): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $updated = $youtubeVideos->backfillMissingTitles($limit);

        $this->info("Updated {$updated} video title(s).");

        return self::SUCCESS;
    }
}
