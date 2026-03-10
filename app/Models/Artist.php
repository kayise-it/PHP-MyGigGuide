<?php

namespace App\Models;

use App\Traits\Claimable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Artist extends Model
{
    use Claimable;
    protected $fillable = [
        'user_id',
        'stage_name',
        'real_name',
        'genre',
        'bio',
        'phone_number',
        'contact_email',
        'email',
        'instagram',
        'facebook',
        'twitter',
        'profile_picture',
        'gallery',
        'settings',
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
        'gallery' => 'array',
        'settings' => 'array',
        'pending_claim_at' => 'datetime',
        'dispute_raised_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'warning_email_sent_at' => 'datetime',
        'dispute_raised' => 'boolean',
    ];

    // Mutator to sanitize profile picture assignment and negate ops storing paths
    public function setProfilePictureAttribute($value)
    {
        if ($value && (strpos($value, '/tmp/php') !== false || strpos($value, 'tmp.php') !== false)) {
            // Log suspicious assignment and set as null (Sprite to unknown path).
            \Log::warning('Artist profile_picture was attempted to store non-final path '.$value);
            $this->attributes['profile_picture'] = null;
        } elseif ($value) {
            $this->attributes['profile_picture'] = $value;
        }
    }

    /**
     * Get the user that owns the artist profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the events for the artist.
     */
    public function events(): BelongsToMany
    {
        // Align with Event model which uses 'event_artist' pivot table
        return $this->belongsToMany(Event::class, 'event_artist');
    }

    /**
     * Get the venues owned by the artist.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class, 'owner_id')->where('owner_type', self::class);
    }

    /**
     * Get the ratings for the artist.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this artist.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_artist_favorites');
    }

    /**
     * Get the genres for this artist.
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'artist_genre');
    }

    /**
     * Generate a unique folder name for the artist based on their stage name.
     */
    public function generateUniqueFolderName()
    {
        $stageName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->stage_name ?? 'artist'));
        $randomSuffix = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        return 'art_' . $stageName . '_' . $randomSuffix;
    }

    /**
     * Get or create the artist's folder settings.
     */
    public function getOrCreateFolderSettings()
    {
        if (empty($this->settings) || ! isset($this->settings['folder_name'])) {
            $folderName = $this->generateUniqueFolderName();
            $settings = $this->settings ?? [];
            $settings['folder_name'] = $folderName;
            $settings['created_at'] = now()->toISOString();

            $this->update(['settings' => $settings]);

            // Create the actual folder structure
            $this->createArtistFolders($folderName);
        }

        return $this->settings;
    }

    /**
     * Create the artist's folder structure in storage.
     */
    public function createArtistFolders($folderName = null)
    {
        $folderName = $folderName ?? $this->settings['folder_name'] ?? $this->generateUniqueFolderName();

        $basePath = 'artists/' . $folderName;

        $folders = [
            $basePath . '/profile',
            $basePath . '/events',
            $basePath . '/galleries',
            $basePath . '/documents',
            $basePath . '/temp',
        ];

        foreach ($folders as $folder) {
            \Storage::disk('public')->makeDirectory($folder);
        }

        return $basePath;
    }

    /**
     * Get the artist's base folder path.
     */
    public function getFolderPath()
    {
        $settings = $this->getOrCreateFolderSettings();

        return 'artists/' . $settings['folder_name'];
    }

    /**
     * Get the YouTube videos for this artist.
     */
    public function youtubeVideos(): MorphMany
    {
        return $this->morphMany(YoutubeVideo::class, 'videoable')->orderBy('order');
    }
}
