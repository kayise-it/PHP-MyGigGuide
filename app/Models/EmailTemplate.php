<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'subject',
        'description',
        'body_html',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Find a template by key.
     */
    public static function forKey(string $key): ?self
    {
        return static::where('key', $key)->where('is_active', true)->first();
    }

    /**
     * Render the template body as Blade with the given data.
     */
    public function render(array $data = []): string
    {
        // Allow using Blade directives / variables inside stored HTML.
        return Blade::render($this->body_html, $data);
    }
}

