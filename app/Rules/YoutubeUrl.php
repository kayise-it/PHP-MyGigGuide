<?php

namespace App\Rules;

use App\Models\YoutubeVideo;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YoutubeUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Allow empty values (use nullable in validation)
        }

        // Extract video ID from URL
        $videoId = YoutubeVideo::extractVideoId($value);

        if (!$videoId) {
            $fail('The :attribute must be a valid YouTube URL.');
        }
    }
}

