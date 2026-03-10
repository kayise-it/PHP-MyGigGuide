<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'color',
        'icon',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the events that belong to this category.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_category');
    }

    /**
     * Get the count of events in this category.
     */
    public function getEventsCountAttribute(): int
    {
        return $this->events()->count();
    }
}




