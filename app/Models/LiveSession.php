<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSession extends Model
{
    public const STATUS_LIVE = 'live';

    public const STATUS_ENDED = 'ended';

    protected $fillable = [
        'artist_id',
        'event_id',
        'started_by_user_id',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(SongRequest::class);
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }
}
