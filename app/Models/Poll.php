<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    protected $fillable = [
        'context',
        'question',
        'options',
        'is_active',
        'closes_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
            'closes_at' => 'datetime',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function isOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->closes_at !== null && $this->closes_at->isPast()) {
            return false;
        }

        return true;
    }

    /** @return array<int, int> option_index => vote count */
    public function voteCounts(): array
    {
        $counts = array_fill(0, count($this->options ?? []), 0);

        foreach ($this->votes()->selectRaw('option_index, count(*) as total')->groupBy('option_index')->get() as $row) {
            $counts[(int) $row->option_index] = (int) $row->total;
        }

        return $counts;
    }
}
