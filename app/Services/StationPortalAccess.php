<?php

namespace App\Services;

use App\Models\User;

/**
 * Who may open the partner station portal on a tenant hostname (919 FM first).
 */
class StationPortalAccess
{
    public static function userCanAccess(?User $user, string $tenantKey): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->hasRole(['admin', 'superuser'])) {
            return true;
        }

        $email = strtolower(trim((string) $user->email));
        if ($email === '') {
            return false;
        }

        return match ($tenantKey) {
            'fm919' => in_array($email, self::fm919StationEmails(), true),
            default => false,
        };
    }

    /**
     * @return list<string> lowercase emails
     */
    public static function fm919StationEmails(): array
    {
        $raw = (string) config('fm919.station_emails', '');

        return array_values(array_unique(array_filter(array_map(
            static fn (string $e) => strtolower(trim($e)),
            explode(',', $raw),
        ))));
    }
}
