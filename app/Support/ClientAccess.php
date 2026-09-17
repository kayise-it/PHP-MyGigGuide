<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * First-party app/site access: which skin (My Gig Guide vs 919 FM, etc.) and
 * which platform (Android, iOS, website). No third-party analytics.
 */
final class ClientAccess
{
    public const TOUCH_AFTER_MINUTES = 15;

    /** @var list<string> */
    public const CLIENTS = [
        'mygigguide',
        'fm919',
        'rogues',
        'hot1027',
        'risefm',
        'vowfm',
        'mixfm',
    ];

    /** @var list<string> */
    public const PLATFORMS = [
        'android',
        'ios',
        'web',
    ];

    public static function normalizeClient(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, self::CLIENTS, true) ? $value : null;
    }

    public static function normalizePlatform(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));

        if ($value === 'iphone' || $value === 'ipad') {
            $value = 'ios';
        }

        return in_array($value, self::PLATFORMS, true) ? $value : null;
    }

    /**
     * @return array{client: ?string, platform: ?string}
     */
    public static function fromRequest(Request $request): array
    {
        $client = self::normalizeClient(
            $request->header('X-Client-Brand')
            ?? $request->input('brand')
            ?? $request->input('client')
        );
        $platform = self::normalizePlatform(
            $request->header('X-Client-Platform')
            ?? $request->input('platform')
        );

        if ($client === null || $platform === null) {
            $parsed = self::parseDeviceName($request->input('device_name'));
            $client ??= $parsed['client'];
            $platform ??= $parsed['platform'];
        }

        return ['client' => $client, 'platform' => $platform];
    }

    /**
     * @return array{client: ?string, platform: ?string}
     */
    public static function parseDeviceName(mixed $deviceName): array
    {
        if (! is_string($deviceName) || $deviceName === '') {
            return ['client' => null, 'platform' => null];
        }

        $parts = explode('-', strtolower(trim($deviceName)));
        if (count($parts) < 2) {
            return ['client' => null, 'platform' => null];
        }

        return [
            'client' => self::normalizeClient($parts[0]),
            'platform' => self::normalizePlatform($parts[1]),
        ];
    }

    public static function suggestedTokenName(?string $client, ?string $platform): ?string
    {
        $client = self::normalizeClient($client);
        $platform = self::normalizePlatform($platform);

        if ($client === null || $platform === null) {
            return null;
        }

        return $client.'-'.$platform;
    }

    /**
     * @return array{client: ?string, platform: string}
     */
    public static function forWeb(?Request $request = null): array
    {
        return [
            'client' => self::normalizeClient(SiteBrand::current($request)->key),
            'platform' => 'web',
        ];
    }

    public static function clientLabel(?string $client): string
    {
        return match ($client) {
            'mygigguide' => 'My Gig Guide',
            'fm919' => '919 FM',
            'rogues' => 'Rogues',
            'hot1027' => 'HOT 102.7',
            'risefm' => 'Rise FM',
            'vowfm' => 'VOW FM',
            'mixfm' => 'Mix FM',
            default => 'Not recorded yet',
        };
    }

    public static function platformLabel(?string $platform): string
    {
        return match ($platform) {
            'android' => 'Android',
            'ios' => 'iOS',
            'web' => 'Website',
            default => 'Not recorded yet',
        };
    }
}
