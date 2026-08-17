<?php

namespace App\Services;

use App\Helpers\NameNormalizer;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared text search for venue directory listings (API + future reuse).
 */
class VenueDirectorySearch
{
    /**
     * @param  Builder<\App\Models\Venue>  $query
     */
    public static function apply(Builder $query, string $rawSearch): void
    {
        $term = trim($rawSearch);
        if ($term === '') {
            return;
        }

        $terms = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $likeTerm = '%'.$term.'%';
        $normalizedTerm = self::normalizeForSearch($term);
        $normalizedLike = $normalizedTerm !== '' ? '%'.$normalizedTerm.'%' : null;
        $compactTerm = NameNormalizer::normalize($term);
        $compactLike = strlen($compactTerm) >= 2 ? '%'.$compactTerm.'%' : null;

        $query->where(function (Builder $q) use ($term, $terms, $likeTerm, $normalizedLike, $normalizedTerm, $compactTerm, $compactLike) {
            $q->where('name', 'like', $likeTerm)
                ->orWhere('city', 'like', $likeTerm)
                ->orWhere('address', 'like', $likeTerm);

            if ($normalizedLike !== null) {
                $q->orWhereRaw(
                    self::normalizedNameSql().' LIKE ?',
                    [$normalizedLike]
                );
            }

            if (count($terms) > 1) {
                $q->orWhere(function (Builder $inner) use ($terms) {
                    foreach ($terms as $word) {
                        if (mb_strlen($word) < 2) {
                            continue;
                        }
                        $inner->where('name', 'like', '%'.$word.'%');
                    }
                });
            }

            // Poster / user typed more than the listing name, e.g. "Higher Ground Restaurant" → "Higher Ground".
            $q->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(TRIM(name)), "%")', [$term]);

            // User typed a prefix of the listing name.
            $q->orWhereRaw('LOWER(TRIM(name)) LIKE CONCAT("%", LOWER(?), "%")', [$term]);

            if ($normalizedLike !== null) {
                $q->orWhereRaw(
                    '? LIKE CONCAT("%", '.self::normalizedNameSql().', "%")',
                    [$normalizedTerm]
                );
            }

            // "Fubar" ↔ "Fu-Bar", "O'Brien" ↔ "OBrien" — same rules as [NameNormalizer].
            if ($compactLike !== null) {
                $compactSql = self::compactNameSql();
                $q->orWhereRaw($compactSql.' LIKE ?', [$compactLike])
                    ->orWhereRaw('? LIKE CONCAT("%", '.$compactSql.', "%")', [$compactTerm]);
            }
        });
    }

    /**
     * Lowercase name with apostrophes removed — "Santi's" matches "santis".
     */
    public static function normalizeForSearch(string $value): string
    {
        $v = mb_strtolower(trim($value));
        $v = str_replace(["'", '’', '`', '´'], '', $v);
        $v = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $v) ?? $v;

        return trim(preg_replace('/\s+/u', ' ', $v) ?? $v);
    }

    private static function normalizedNameSql(): string
    {
        $expr = 'LOWER(TRIM(name))';
        foreach (["'", '’', '`', '´'] as $char) {
            $expr = $char === "'"
                ? "REPLACE($expr, '''', '')"
                : 'REPLACE('.$expr.", '".$char."', '')";
        }
        foreach (['-', '–', '—'] as $char) {
            $expr = 'REPLACE('.$expr.", '".$char."', '')";
        }

        return $expr;
    }

    /**
     * Alphanumeric-only lowercase name — "Fu Bar" and "Fu-Bar" both become `fubar`.
     * Uses REGEXP_REPLACE (MariaDB/MySQL 8+). Avoids nested REPLACE with `#` (SQL comment char).
     */
    private static function compactNameSql(): string
    {
        return "REGEXP_REPLACE(LOWER(TRIM(name)), '[^a-z0-9]', '')";
    }
}
