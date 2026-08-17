<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared text search for artist directory listings (API + future reuse).
 */
class ArtistDirectorySearch
{
    /**
     * @param  Builder<\App\Models\Artist>  $query
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

        $query->where(function (Builder $q) use ($term, $terms, $likeTerm, $normalizedLike, $normalizedTerm) {
            foreach (['stage_name', 'real_name', 'genre'] as $column) {
                $q->orWhere($column, 'like', $likeTerm);
            }

            if ($normalizedLike !== null) {
                foreach (['stage_name', 'real_name'] as $column) {
                    $q->orWhereRaw(
                        self::normalizedColumnSql($column).' LIKE ?',
                        [$normalizedLike]
                    );
                }
            }

            if (count($terms) > 1) {
                $q->orWhere(function (Builder $inner) use ($terms) {
                    foreach ($terms as $word) {
                        if (mb_strlen($word) < 2) {
                            continue;
                        }
                        $inner->where(function (Builder $wordQ) use ($word) {
                            $like = '%'.$word.'%';
                            $wordQ->where('stage_name', 'like', $like)
                                ->orWhere('real_name', 'like', $like);
                        });
                    }
                });
            }

            foreach (['stage_name', 'real_name'] as $column) {
                $q->orWhereRaw(
                    'LOWER(?) LIKE CONCAT("%", LOWER(TRIM('.$column.')), "%")',
                    [$term]
                );
                $q->orWhereRaw(
                    'LOWER(TRIM('.$column.')) LIKE CONCAT("%", LOWER(?), "%")',
                    [$term]
                );
            }

            if ($normalizedLike !== null) {
                foreach (['stage_name', 'real_name'] as $column) {
                    $q->orWhereRaw(
                        '? LIKE CONCAT("%", '.self::normalizedColumnSql($column).', "%")',
                        [$normalizedTerm]
                    );
                }
            }
        });
    }

    public static function normalizeForSearch(string $value): string
    {
        $v = mb_strtolower(trim($value));
        $v = str_replace(["'", '’', '`', '´'], '', $v);
        $v = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $v) ?? $v;

        return trim(preg_replace('/\s+/u', ' ', $v) ?? $v);
    }

    private static function normalizedColumnSql(string $column): string
    {
        return "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM({$column}), '''', ''), '’', ''), '`', ''), '´', ''))";
    }
}
