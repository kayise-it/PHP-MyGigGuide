<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @return static
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Cast the value based on its type.
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue(string $value, string $type)
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'array', 'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Check if Facebook login is enabled.
     *
     * @return bool
     */
    public static function isFacebookLoginEnabled(): bool
    {
        return static::get('facebook_login_enabled', false);
    }

    /**
     * Check if paid features are enabled.
     *
     * @return bool
     */
    public static function isPaidFeaturesEnabled(): bool
    {
        return static::get('paid_features_enabled', false);
    }
}

