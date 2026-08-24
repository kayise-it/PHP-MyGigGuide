<?php

namespace App\Models;

use App\Traits\Claimable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use Claimable;
    protected $fillable = [
        'name',
        'description',
        'date',
        'time',
        'price',
        'ticket_url',
        'tiktok',
        'poster',
        'poster_card',
        'gallery',
        'status',
        'category',
        'capacity',
        'venue_id',
        'owner_id',
        'owner_type',
        // Claim fields
        'pending_claim_user_id',
        'pending_claim_at',
        'dispute_raised',
        'dispute_raised_at',
        'dispute_reason',
        'claim_status',
        'grace_period_ends_at',
        'warning_email_sent_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'time' => 'datetime',
        'gallery' => 'array',
        // Claim field casts
        'pending_claim_at' => 'datetime',
        'dispute_raised_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'warning_email_sent_at' => 'datetime',
        'dispute_raised' => 'boolean',
    ];

    // Mutator to handle time field conversion
    public function setTimeAttribute($value)
    {
        if ($value && is_string($value)) {
            // If it's just time format (H:i), combine with today's date
            if (preg_match('/^\d{2}:\d{2}$/', $value)) {
                $this->attributes['time'] = now()->format('Y-m-d') . ' ' . $value;
            } else {
                $this->attributes['time'] = $value;
            }
        } else {
            $this->attributes['time'] = $value;
        }
    }

    // Accessor to format time for display
    public function getTimeAttribute($value)
    {
        if ($value) {
            return \Carbon\Carbon::parse($value);
        }
        return $value;
    }

    // Mutator to sanitize gallery assignment and prevent storing temp paths
    public function setGalleryAttribute($value)
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $path) {
                if ($path && (strpos($path, '/tmp/php') !== false || strpos($path, 'tmp.php') !== false)) {
                    \Log::warning('Event gallery was attempted to store non-final path: '.$path);

                    continue; // Skip invalid temp paths
                } elseif ($path) {
                    $sanitized[] = $path;
                }
            }
            $this->attributes['gallery'] = json_encode($sanitized);
        } elseif ($value && (strpos($value, '/tmp/php') !== false || strpos($value, 'tmp.php') !== false)) {
            \Log::warning('Event gallery was attempted to store non-final path: '.$value);
            $this->attributes['gallery'] = null;
        } else {
            $this->attributes['gallery'] = $value;
        }
    }

    // Mutator to sanitize poster assignment and prevent storing temp paths
    public function setPosterAttribute($value)
    {
        if ($value && (strpos($value, '/tmp/php') !== false || strpos($value, 'tmp.php') !== false)) {
            \Log::warning('Event poster was attempted to store non-final path: '.$value);
            $this->attributes['poster'] = null;
        } elseif ($value) {
            $this->attributes['poster'] = $value;
        }
    }

    /**
     * Get the venue where the event is held.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the owner of the event (Artist or Organiser).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the artists performing at this event.
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'event_artist');
    }

    public function liveSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LiveSession::class);
    }

    public function checkIns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EventCheckIn::class);
    }

    /**
     * Get the ratings for the event.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this event.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_event_favorites');
    }

    /**
     * Get the genres for this event.
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'event_genre');
    }

    /**
     * Get the categories for this event.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'event_category');
    }

    /**
     * Get the YouTube videos for this event.
     */
    public function youtubeVideos(): MorphMany
    {
        return $this->morphMany(YoutubeVideo::class, 'videoable')->orderBy('order');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Whether the gig date/time is in the past (status is ignored).
     */
    public function hasTakenPlace(?Carbon $now = null): bool
    {
        if ($this->date === null) {
            return false;
        }

        $now ??= now();
        $day = $this->date->toDateString();

        if ($day < $now->toDateString()) {
            return true;
        }

        if ($day > $now->toDateString()) {
            return false;
        }

        if ($this->time === null) {
            return false;
        }

        return Carbon::parse($this->time)->format('H:i:s') < $now->format('H:i:s');
    }

    /**
     * @param  Builder<Event>  $query
     */
    public function scopeWhereTakenPlace(Builder $query, ?Carbon $now = null): void
    {
        $now ??= now();

        $query->whereNotNull('date')
            ->where(function (Builder $q) use ($now) {
                $q->whereDate('date', '<', $now->toDateString())
                    ->orWhere(function (Builder $inner) use ($now) {
                        $inner->whereDate('date', '=', $now->toDateString())
                            ->whereNotNull('time')
                            ->whereTime('time', '<', $now->format('H:i:s'));
                    });
            });
    }

    /**
     * @param  Builder<Event>  $query
     */
    public function scopeWhereNotCancelled(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereNull('status')
                ->orWhere('status', '!=', 'cancelled');
        });
    }

    /**
     * Crowd-sourced / app-posted listings — excludes Quicket import rows.
     *
     * @param  Builder<Event>  $query
     */
    public function scopeUserPosted(Builder $query): Builder
    {
        $query->where(function (Builder $q) {
            $q->whereNull('ticket_url')
                ->orWhere('ticket_url', 'not like', '%quicket.co.za%');
        });

        $quicketOwnerId = config('quicket.owner_user_id');
        if ($quicketOwnerId) {
            $query->whereNot(function (Builder $q) use ($quicketOwnerId) {
                $q->where('owner_id', $quicketOwnerId)
                    ->where(function (Builder $inner) {
                        $inner->where('owner_type', 'user')
                            ->orWhere('owner_type', User::class)
                            ->orWhere('owner_type', 'App\Models\User');
                    });
            });
        }

        return $query;
    }
}
