<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'rateable_id',
        'rateable_type',
        'rating',
        'review',
    ];

    /**
     * Get the user that made the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the rateable entity (Artist, Event, Venue, or Organiser).
     */
    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }
}
