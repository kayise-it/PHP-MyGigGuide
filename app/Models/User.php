<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRolesAndPermissions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'auth_provider',
        'auth_provider_id',
        'firebase_uid',
        'profile_picture',
        'settings',
        'email_verified_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the artist profile associated with the user.
     */
    public function artist()
    {
        return $this->hasOne(Artist::class);
    }

    /**
     * Get the organiser profile associated with the user.
     */
    public function organiser()
    {
        return $this->hasOne(Organiser::class);
    }

    /**
     * Get the venues owned by the user.
     */
    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    /**
     * Get the events created by the user.
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'owner');
    }

    /**
     * Get the ratings given by the user.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get user's favorite events.
     */
    public function favoriteEvents()
    {
        return $this->belongsToMany(Event::class, 'user_event_favorites');
    }

    /**
     * Get user's favorite venues.
     */
    public function favoriteVenues()
    {
        return $this->belongsToMany(Venue::class, 'user_venue_favorites');
    }

    /**
     * Get user's favorite artists.
     */
    public function favoriteArtists()
    {
        return $this->belongsToMany(Artist::class, 'user_artist_favorites');
    }

    /**
     * Get user's favorite organisers.
     */
    public function favoriteOrganisers()
    {
        return $this->belongsToMany(Organiser::class, 'user_organiser_favorites');
    }

    /**
     * Get venues owned by this user (many-to-many relationship).
     */
    public function ownedVenues()
    {
        return $this->belongsToMany(Venue::class, 'venue_owners')
            ->withPivot('role', 'added_by_user_id', 'added_at')
            ->withTimestamps();
    }

    /**
     * Get venue ownership requests made by this user.
     */
    public function venueOwnershipRequests()
    {
        return $this->hasMany(\App\Models\VenueOwnerRequest::class, 'requester_user_id');
    }

    /**
     * Generate a unique folder name for the user based on their role and username.
     */
    public function generateUniqueFolderName()
    {
        $rolePrefix = $this->getRolePrefix();
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->username));
        $randomSuffix = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        return $rolePrefix.'_'.$username.'_'.$randomSuffix;
    }

    /**
     * Get the role prefix for folder naming.
     */
    private function getRolePrefix()
    {
        if ($this->hasRole('artist')) {
            return 'art';
        } elseif ($this->hasRole('organiser')) {
            return 'org';
        } else {
            return 'usa'; // user
        }
    }

    /**
     * Get or create the user's folder settings.
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
            $this->createUserFolders($folderName);
        }

        return $this->settings;
    }

    /**
     * Create the user's folder structure in storage.
     */
    public function createUserFolders($folderName = null)
    {
        $folderName = $folderName ?? $this->settings['folder_name'] ?? $this->generateUniqueFolderName();

        // Determine role-based folder structure
        $roleFolder = $this->getRoleFolder();
        $basePath = $roleFolder.'/'.$folderName;

        $folders = [
            $basePath.'/profile',
            $basePath.'/events',
            $basePath.'/galleries',
            $basePath.'/documents',
            $basePath.'/temp',
        ];

        foreach ($folders as $folder) {
            \Storage::disk('public')->makeDirectory($folder);
        }

        return $basePath;
    }

    /**
     * Get the role-based folder name.
     */
    private function getRoleFolder()
    {
        if ($this->hasRole('artist')) {
            return 'artists';
        } elseif ($this->hasRole('organiser')) {
            return 'organisers';
        } else {
            return 'users';
        }
    }

    /**
     * Get the user's base folder path.
     */
    public function getFolderPath()
    {
        $settings = $this->getOrCreateFolderSettings();
        $roleFolder = $this->getRoleFolder();

        return $roleFolder.'/'.$settings['folder_name'];
    }

    /**
     * Ensure related profile rows exist for the user's assigned roles.
     */
    public function ensureRoleProfiles()
    {
        $this->loadMissing('roles');

        foreach ($this->roles as $role) {
            $roleName = $role->name;

            if ($roleName === 'artist') {
                \App\Models\Artist::firstOrCreate(
                    ['user_id' => $this->id],
                    ['stage_name' => $this->name]
                );
            }

            if ($roleName === 'organiser') {
                \App\Models\Organiser::firstOrCreate(
                    ['user_id' => $this->id],
                    [
                        'organisation_name' => $this->name,
                        'contact_email' => $this->email,
                    ]
                );
            }
        }
    }
}
