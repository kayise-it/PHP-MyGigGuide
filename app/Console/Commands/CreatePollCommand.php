<?php

namespace App\Console\Commands;

use App\Models\Poll;
use Illuminate\Console\Command;

class CreatePollCommand extends Command
{
    protected $signature = 'poll:create
        {context : Station context e.g. mix938, vowfm}
        {question : Poll question}
        {options* : Two or more answer options}';

    protected $description = 'Create a poll for a station/app context';

    public function handle(): int
    {
        $context = $this->argument('context');
        $question = $this->argument('question');
        $options = $this->argument('options');

        if (count($options) < 2) {
            $this->error('Provide at least two options.');

            return self::FAILURE;
        }

        Poll::query()
            ->where('context', $context)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'closes_at' => now(),
            ]);

        $poll = Poll::create([
            'context' => $context,
            'question' => $question,
            'options' => $options,
            'is_active' => true,
        ]);

        $this->info("Poll #{$poll->id} created for context [{$context}].");

        return self::SUCCESS;
    }
}
