<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_PLAYED = 'played';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'live_session_id',
        'user_id',
        'artist_song_id',
        'message',
        'status',
        'tip_reference',
        'tip_amount_zar',
    ];

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artistSong(): BelongsTo
    {
        return $this->belongsTo(ArtistSong::class);
    }
}
