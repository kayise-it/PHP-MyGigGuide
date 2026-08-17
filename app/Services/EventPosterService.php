<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;

class EventPosterService
{
    /**
     * Public contributor info for an event listing.
     *
     * @return array{
     *     name: string,
     *     username: ?string,
     *     via: ?string,
     *     owner_type: ?string,
     *     page: ?array{type: string, id: int, name: string, url: string}
     * }|null
     */
    public function serializePostedBy(Event $event): ?array
    {
        $event->loadMissing('owner');

        $ownerType = $this->normalizeOwnerType($event->owner_type);
        if ($ownerType === null) {
            return null;
        }

        $user = null;
        $via = null;
        $page = null;

        if ($ownerType === 'user') {
            $user = $event->owner instanceof User
                ? $event->owner
                : User::query()->find($event->owner_id);
        } elseif ($ownerType === 'artist') {
            $artist = $this->resolveArtistOwner($event);

            if ($artist) {
                $via = $artist->getDisplayName();
                $user = $artist->user;
                $page = $this->pageForArtist($artist);
            }
            $user ??= User::query()->find($event->owner_id);
        } elseif ($ownerType === 'organiser') {
            $organiser = $this->resolveOrganiserOwner($event);

            if ($organiser) {
                $via = $organiser->getDisplayName();
                $user = $organiser->user;
                $page = $this->pageForOrganiser($organiser);
            }
            $user ??= User::query()->find($event->owner_id);
        }

        $name = $this->displayNameForUser($user);
        if ($name === null && $via !== null) {
            $name = $via;
            $via = null;
        }

        if ($name === null) {
            return null;
        }

        if ($via !== null && strcasecmp($via, $name) === 0) {
            $via = null;
        }

        if ($user !== null && $this->shouldHidePostedBy($user)) {
            return null;
        }

        return [
            'name' => $name,
            'username' => $user?->username,
            'via' => $via,
            'owner_type' => $ownerType,
            'page' => $page,
        ];
    }

    /**
     * @return array{type: string, id: int, name: string, url: string}
     */
    private function pageForArtist(Artist $artist): array
    {
        return [
            'type' => 'artist',
            'id' => $artist->id,
            'name' => $artist->getDisplayName(),
            'url' => route('artists.show', $artist),
        ];
    }

    /**
     * @return array{type: string, id: int, name: string, url: string}
     */
    private function pageForOrganiser(Organiser $organiser): array
    {
        return [
            'type' => 'organiser',
            'id' => $organiser->id,
            'name' => $organiser->getDisplayName(),
            'url' => route('organisers.show', $organiser),
        ];
    }

    private function shouldHidePostedBy(User $user): bool
    {
        $user->loadMissing('roles');

        return $user->hasRole(['superuser', 'admin']);
    }

    private function displayNameForUser(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $name = trim((string) $user->name);
        if ($name !== '') {
            return $name;
        }

        $username = trim((string) $user->username);
        if ($username !== '') {
            return $username;
        }

        return null;
    }

    private function resolveArtistOwner(Event $event): ?Artist
    {
        if ($event->owner instanceof Artist) {
            return $event->owner;
        }

        $artist = Artist::query()->find($event->owner_id);
        if ($artist !== null) {
            return $artist;
        }

        // Legacy create bug: owner_type artist but owner_id was the user id.
        return Artist::query()->where('user_id', $event->owner_id)->first();
    }

    private function resolveOrganiserOwner(Event $event): ?Organiser
    {
        if ($event->owner instanceof Organiser) {
            return $event->owner;
        }

        $organiser = Organiser::query()->find($event->owner_id);
        if ($organiser !== null) {
            return $organiser;
        }

        return Organiser::query()->where('user_id', $event->owner_id)->first();
    }

    private function normalizeOwnerType(?string $ownerType): ?string
    {
        if ($ownerType === null || $ownerType === '') {
            return null;
        }

        return match ($ownerType) {
            'user', 'artist', 'organiser' => $ownerType,
            'admin', 'superuser' => 'user',
            User::class, 'App\Models\User' => 'user',
            Artist::class, 'App\Models\Artist' => 'artist',
            Organiser::class, 'App\Models\Organiser' => 'organiser',
            default => match (class_basename($ownerType)) {
                'User' => 'user',
                'Artist' => 'artist',
                'Organiser' => 'organiser',
                default => null,
            },
        };
    }
}
