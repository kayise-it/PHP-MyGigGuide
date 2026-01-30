<?php

namespace App\Helpers;

class NameNormalizer
{
    /**
     * Normalize a name for uniqueness comparison.
     *
     * This function:
     * - Converts to lowercase
     * - Removes accents/diacritics
     * - Removes hyphens, spaces, and special characters
     * - Keeps only alphanumeric characters
     *
     * @param string|null $name
     * @return string
     */
    public static function normalize(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        // Convert to lowercase
        $normalized = mb_strtolower($name, 'UTF-8');

        // Remove accents/diacritics using transliterator if available
        if (function_exists('transliterator_transliterate')) {
            $normalized = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $normalized);
        } else {
            // Fallback: manual accent removal for common characters
            $normalized = self::removeAccentsManual($normalized);
        }

        // Remove all non-alphanumeric characters (keeps letters and numbers only)
        $normalized = preg_replace('/[^a-z0-9]/u', '', $normalized);

        return $normalized;
    }

    /**
     * Check if two names are considered duplicates after normalization.
     *
     * @param string|null $name1
     * @param string|null $name2
     * @return bool
     */
    public static function areDuplicates(?string $name1, ?string $name2): bool
    {
        return self::normalize($name1) === self::normalize($name2);
    }

    /**
     * Manual accent removal fallback for systems without intl extension.
     *
     * @param string $string
     * @return string
     */
    private static function removeAccentsManual(string $string): string
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'þ' => 'th',
            'ß' => 'ss',
        ];

        return strtr($string, $accents);
    }
}


