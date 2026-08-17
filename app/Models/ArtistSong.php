<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistSong extends Model
{
    protected $fillable = [
        'artist_id',
        'title',
        'original_artist',
        'is_original',
        'sort_order',
        'reference_youtube_id',
        'reference_spotify_id',
        'notes',
    ];

    protected $casts = [
        'is_original' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }
}
