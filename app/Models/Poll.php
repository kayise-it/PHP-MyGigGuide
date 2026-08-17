<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    protected $fillable = [
        'context',
        'question',
        'options',
        'active',
        'closes_at',
    ];

    protected $casts = [
        'options'   => 'array',
        'active'    => 'boolean',
        'closes_at' => 'datetime',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /** Active poll for a given context (not expired). */
    public function scopeActiveForContext(Builder $query, string $context): void
    {
        $query->where('context', $context)
              ->where('active', true)
              ->where(function (Builder $q) {
                  $q->whereNull('closes_at')
                    ->orWhere('closes_at', '>', now());
              });
    }

    /** Total votes cast across all options. */
    public function totalVotes(): int
    {
        return $this->votes()->count();
    }

    /**
     * Returns per-option summary: label, vote count, percentage.
     *
     * @return array<int, array{label: string, votes: int, percent: float}>
     */
    public function resultsArray(): array
    {
        $total = $this->totalVotes();
        $counts = $this->votes()
            ->selectRaw('option_index, COUNT(*) as cnt')
            ->groupBy('option_index')
            ->pluck('cnt', 'option_index')
            ->all();

        $results = [];
        foreach ($this->options as $index => $label) {
            $votes = (int) ($counts[$index] ?? 0);
            $results[] = [
                'label'   => $label,
                'votes'   => $votes,
                'percent' => $total > 0 ? round($votes / $total * 100, 1) : 0.0,
            ];
        }

        return $results;
    }
}
