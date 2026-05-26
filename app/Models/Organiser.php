<?php

namespace App\Models;

use App\Traits\Claimable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Organiser extends Model
{
    use Claimable;

    protected $fillable = [
        'user_id',
        'organisation_name',
        'contact_email',
        'phone_number',
        'website',
        'description',
        'logo',
        'settings',
        // Claim fields
        'pending_claim_user_id',
        'pending_claim_at',
        'dispute_raised',
        'dispute_raised_at',
        'dispute_reason',
        'claim_request_message',
        'claim_status',
        'grace_period_ends_at',
        'warning_email_sent_at',
    ];

    protected $casts = [
        'settings' => 'array',
        // Claim field casts
        'pending_claim_at' => 'datetime',
        'dispute_raised_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'warning_email_sent_at' => 'datetime',
        'dispute_raised' => 'boolean',
    ];

    /**
     * Get the user that owns the organiser profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the events created by the organiser.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'owner_id')->where('owner_type', self::class);
    }

    /**
     * Get the venues owned by the organiser.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class, 'owner_id')->where('owner_type', self::class);
    }

    /**
     * Get the ratings for the organiser.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this organiser.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organiser_favorites');
    }
}
