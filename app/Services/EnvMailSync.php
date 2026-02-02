<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class EnvMailSync
{
    /**
     * Update .env mail variables so Laravel uses the given mail account.
     * Call this when the "default" mail account is updated in Admin > Mail Accounts.
     *
     * @param  string  $email  Full email address (e.g. noreply@mygigguide.co.za)
     * @param  string|null  $plainPassword  Plain password; omit to leave MAIL_PASSWORD unchanged
     * @return bool  True if .env was written successfully
     */
    public static function syncFromMailAccount(string $email, ?string $plainPassword = null): bool
    {
        $path = base_path('.env');
        if (! File::exists($path)) {
            return false;
        }

        $content = File::get($path);
        $replacements = [
            'MAIL_USERNAME' => $email,
            'MAIL_FROM_ADDRESS' => $email,
        ];
        if ($plainPassword !== null) {
            $replacements['MAIL_PASSWORD'] = $plainPassword;
        }

        foreach ($replacements as $key => $value) {
            $escaped = self::escapeEnvValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
            $replacement = $key . '=' . $escaped;
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content, 1);
            } else {
                $content .= "\n" . $replacement . "\n";
            }
        }

        return File::put($path, $content) !== false;
    }

    /**
     * Escape a value for use in .env (double-quoted if needed).
     */
    protected static function escapeEnvValue(string $value): string
    {
        if ($value === '' || preg_match('/^[a-zA-Z0-9._-]+$/', $value)) {
            return $value;
        }
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }
}
