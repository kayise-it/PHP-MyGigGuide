<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the events that belong to this genre.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_genre');
    }

    /**
     * Get the artists that belong to this genre.
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_genre');
    }
}










