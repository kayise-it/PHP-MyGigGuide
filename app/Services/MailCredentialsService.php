<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

class MailCredentialsService
{
    protected static string $path = 'mail_credentials.json';

    /**
     * Store SMTP credentials (encrypted) so the app uses them instead of .env.
     * Call when the default mail account is updated in Admin > Mail Accounts.
     */
    public static function store(string $email, ?string $plainPassword = null): bool
    {
        $storagePath = storage_path('app/' . self::$path);

        $data = [
            'email' => $email,
        ];

        if ($plainPassword !== null) {
            $data['password_encrypted'] = Crypt::encryptString($plainPassword);
        } else {
            // Keep existing encrypted password when only email changed
            $existing = self::readRaw();
            if ($existing && ! empty($existing['password_encrypted'])) {
                $data['password_encrypted'] = $existing['password_encrypted'];
            } else {
                return false;
            }
        }

        return File::put($storagePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Read and decrypt credentials, or null if not set.
     *
     * @return array{email: string, password: string}|null
     */
    public static function get(): ?array
    {
        $raw = self::readRaw();
        if (! $raw || empty($raw['email']) || empty($raw['password_encrypted'])) {
            return null;
        }
        try {
            return [
                'email' => $raw['email'],
                'password' => Crypt::decryptString($raw['password_encrypted']),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Apply stored credentials to config so Laravel's mailer uses them.
     * Call from AppServiceProvider::boot().
     */
    public static function applyToConfig(): void
    {
        // Always use failover so registration/verification mail can still go out
        // when upstream SMTP intermittently rejects recipients.
        config([
            'mail.default' => 'failover',
            'mail.mailers.failover.mailers' => ['smtp', 'sendmail', 'log'],
        ]);

        $credentials = self::get();
        if ($credentials !== null) {
            config([
                'mail.mailers.smtp.username' => $credentials['email'],
                'mail.mailers.smtp.password' => $credentials['password'],
                'mail.from.address' => $credentials['email'],
            ]);
            return;
        }
        // Fallback when credentials file doesn't exist: use .env so mail works until file is created.
        // Read env() directly so we see current .env even when config is cached (config:cache).
        $envUser = env('MAIL_USERNAME');
        $envPass = env('MAIL_PASSWORD');
        if ($envUser !== null && $envUser !== '' && $envPass !== null && $envPass !== '') {
            config([
                'mail.mailers.smtp.username' => $envUser,
                'mail.mailers.smtp.password' => $envPass,
                'mail.from.address' => env('MAIL_FROM_ADDRESS') ?: $envUser,
            ]);
        }
    }

    /**
     * Read raw file contents (no decryption).
     *
     * @return array{email?: string, password_encrypted?: string}|null
     */
    protected static function readRaw(): ?array
    {
        $path = storage_path('app/' . self::$path);
        if (! File::exists($path)) {
            return null;
        }
        $content = File::get($path);
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }
}
