<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $fillable = [
        'artist_id',
        'type',
        'path',
        'disk',
        'width',
        'height',
        'size_bytes',
        'checksum_sha1',
        'order_index',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'size_bytes' => 'integer',
        'order_index' => 'integer',
    ];

    /**
     * Get the artist that owns the media.
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * Get the full URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        return \Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the full path for the media file.
     */
    public function getFullPathAttribute(): string
    {
        return \Storage::disk($this->disk)->path($this->path);
    }
}




