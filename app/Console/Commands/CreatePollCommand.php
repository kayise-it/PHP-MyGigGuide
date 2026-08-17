<?php

namespace App\Console\Commands;

use App\Models\Poll;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreatePollCommand extends Command
{
    protected $signature = 'poll:create';

    protected $description = 'Interactively create a new station poll';

    private const KNOWN_CONTEXTS = ['vowfm', 'risefm', 'hot1027', 'fm919', 'mix938'];

    public function handle(): int
    {
        $this->info('--- Create Station Poll ---');

        // 1. Context
        $context = $this->choice(
            'Which station?',
            self::KNOWN_CONTEXTS,
            0,
        );

        // 2. Question
        $question = $this->ask('Poll question');
        if (blank($question)) {
            $this->error('Question cannot be empty.');
            return self::FAILURE;
        }

        // 3. Options (one per line, blank to finish)
        $options = [];
        $this->info('Enter options one by one. Leave blank to finish (minimum 2).');
        $i = 1;
        while (true) {
            $option = $this->ask("Option $i");
            if (blank($option)) {
                if (count($options) < 2) {
                    $this->warn('You need at least 2 options. Keep going.');
                    continue;
                }
                break;
            }
            $options[] = $option;
            $i++;
        }

        // 4. Closes at
        $defaultDate = now()->addDays(7)->format('Y-m-d H:i:s');
        $closesAtRaw = $this->ask("Closes at (Y-m-d H:i:s, or just Y-m-d)", $defaultDate);
        try {
            $closesAt = Carbon::parse($closesAtRaw);
        } catch (\Exception) {
            $this->error("Could not parse date: $closesAtRaw");
            return self::FAILURE;
        }

        // Deactivate any existing active poll for this context first.
        $deactivated = Poll::where('context', $context)->where('active', true)->count();
        if ($deactivated > 0 && ! $this->confirm("There is/are $deactivated active poll(s) for '$context'. Deactivate them and create the new one?", true)) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }
        Poll::where('context', $context)->where('active', true)->update(['active' => false]);

        $poll = Poll::create([
            'context'    => $context,
            'question'   => $question,
            'options'    => $options,
            'active'     => true,
            'closes_at'  => $closesAt,
        ]);

        $this->info('');
        $this->info("Poll created (ID: {$poll->id})");
        $this->table(
            ['Field', 'Value'],
            [
                ['Context',   $poll->context],
                ['Question',  $poll->question],
                ['Options',   implode(' / ', $poll->options)],
                ['Closes at', $poll->closes_at->toDateTimeString()],
            ],
        );

        return self::SUCCESS;
    }
}
