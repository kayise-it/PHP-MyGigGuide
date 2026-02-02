<?php

namespace App\Models;

use App\Traits\Claimable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Venue extends Model
{
    use Claimable;
    // #region agent log
    protected static function booted()
    {
        static::updating(function ($venue) {
            $original = $venue->getOriginal();
            $changes = $venue->getDirty();
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode([
                'location' => 'Venue::updating',
                'message' => 'Venue model updating event triggered',
                'data' => [
                    'venue_id' => $venue->id,
                    'venue_name' => $venue->name,
                    'original_contact_email' => $original['contact_email'] ?? 'NOT_SET',
                    'new_contact_email' => $venue->contact_email,
                    'dirty_fields' => array_keys($changes),
                    'contact_email_changing' => array_key_exists('contact_email', $changes),
                    'backtrace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10))->map(fn($t) => ($t['class'] ?? '') . '::' . ($t['function'] ?? '') . ' (' . basename($t['file'] ?? '') . ':' . ($t['line'] ?? '') . ')')->toArray(),
                ],
                'timestamp' => now()->timestamp * 1000,
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'MODEL',
            ]) . "\n", FILE_APPEND | LOCK_EX);
        });

        static::creating(function ($venue) {
            @file_put_contents('/var/www/mygigguide/.cursor/debug.log', json_encode([
                'location' => 'Venue::creating',
                'message' => 'Venue model creating event triggered',
                'data' => [
                    'venue_name' => $venue->name,
                    'contact_email' => $venue->contact_email,
                    'backtrace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10))->map(fn($t) => ($t['class'] ?? '') . '::' . ($t['function'] ?? '') . ' (' . basename($t['file'] ?? '') . ':' . ($t['line'] ?? '') . ')')->toArray(),
                ],
                'timestamp' => now()->timestamp * 1000,
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'MODEL',
            ]) . "\n", FILE_APPEND | LOCK_EX);
        });
    }
    // #endregion

    protected $fillable = [
        'name',
        'description',
        'city',
        'capacity',
        'contact_email',
        'phone_number',
        'website',
        'address',
        'latitude',
        'longitude',
        'user_id',
        'owner_id',
        'owner_type',
        'main_picture',
        'venue_gallery',
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
        'venue_gallery' => 'array',
        'user_id' => 'integer',
        'owner_id' => 'integer',
        'capacity' => 'integer',
        // Claim field casts
        'pending_claim_at' => 'datetime',
        'dispute_raised_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'warning_email_sent_at' => 'datetime',
        'dispute_raised' => 'boolean',
    ];

    /**
     * Get the user that created the venue.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owner of the venue (Artist or Organiser).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the events held at this venue.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the ratings for the venue.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this venue.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_venue_favorites');
    }

    /**
     * Get the YouTube videos for this venue.
     */
    public function youtubeVideos(): MorphMany
    {
        return $this->morphMany(YoutubeVideo::class, 'videoable')->orderBy('order');
    }

    /**
     * Get all users who own this venue (many-to-many relationship).
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'venue_owners')
            ->withPivot('role', 'added_by_user_id', 'added_at')
            ->withTimestamps();
    }

    /**
     * Get the primary owner of this venue.
     */
    public function primaryOwner()
    {
        return $this->owners()->wherePivot('role', 'primary')->first();
    }

    /**
     * Get all co-owners of this venue.
     */
    public function coOwners()
    {
        return $this->owners()->wherePivot('role', 'co_owner')->get();
    }

    /**
     * Get all managers of this venue.
     */
    public function managers()
    {
        return $this->owners()->wherePivot('role', 'manager')->get();
    }

    /**
     * Check if a user is an owner of this venue.
     */
    public function isOwnedBy(int $userId): bool
    {
        // First check new venue_owners table (any role: primary, co_owner, manager).
        // If the table or connection is missing, we silently fall back to legacy checks.
        try {
            if (\Illuminate\Support\Facades\DB::table('venue_owners')
                ->where('venue_id', $this->id)
                ->where('user_id', $userId)
                ->exists()) {
                return true;
            }
        } catch (\Throwable $e) {
            // Ignore and fall through to legacy ownership logic.
        }
        
        // Fallback to legacy ownership check
        // Check direct user_id match (most common case)
        if ($this->user_id === $userId) {
            return true;
        }
        
        // Check owner_id with User type
        if ($this->owner_id && $this->owner_type === User::class && $this->owner_id === $userId) {
            return true;
        }
        
        // Check owner_id with Artist/Organiser type (check their user_id)
        if ($this->owner_id && $this->owner_type) {
            try {
                if (str_contains($this->owner_type, 'Artist')) {
                    $artist = \App\Models\Artist::find($this->owner_id);
                    if ($artist && isset($artist->user_id) && $artist->user_id === $userId) {
                        return true;
                    }
                } elseif (str_contains($this->owner_type, 'Organiser')) {
                    $organiser = \App\Models\Organiser::find($this->owner_id);
                    if ($organiser && isset($organiser->user_id) && $organiser->user_id === $userId) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                // Models might not exist, continue
            }
        }
        
        return false;
    }

    /**
     * Check if a user is the primary owner of this venue.
     */
    public function isPrimaryOwner(int $userId): bool
    {
        return $this->owners()
            ->where('user_id', $userId)
            ->wherePivot('role', 'primary')
            ->exists();
    }

    /**
     * Get all ownership requests for this venue.
     */
    public function ownershipRequests(): HasMany
    {
        return $this->hasMany(VenueOwnerRequest::class);
    }

    /**
     * Get pending ownership requests for this venue.
     */
    public function pendingOwnershipRequests(): HasMany
    {
        return $this->hasMany(VenueOwnerRequest::class)->where('status', 'pending');
    }
}
