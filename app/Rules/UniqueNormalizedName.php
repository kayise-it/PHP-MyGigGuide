<?php

namespace App\Rules;

use App\Helpers\NameNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueNormalizedName implements ValidationRule
{
    /**
     * The database table to check against.
     */
    protected string $table;

    /**
     * The column name to check.
     */
    protected string $column;

    /**
     * The ID to exclude from the check (for updates).
     */
    protected ?int $excludeId;

    /**
     * The entity type for error messages.
     */
    protected string $entityType;

    /**
     * Create a new rule instance.
     *
     * @param string $table The database table name
     * @param string $column The column to check for uniqueness
     * @param int|null $excludeId The ID to exclude (for updates)
     * @param string|null $entityType The entity type for error messages (defaults to table name)
     */
    public function __construct(string $table, string $column, ?int $excludeId = null, ?string $entityType = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->excludeId = $excludeId;
        $this->entityType = $entityType ?? $this->guessEntityType($table);
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $normalizedInput = NameNormalizer::normalize($value);

        if (empty($normalizedInput)) {
            return;
        }

        // Get all existing names from the table
        $query = DB::table($this->table)->select(['id', $this->column]);

        if ($this->excludeId !== null) {
            $query->where('id', '!=', $this->excludeId);
        }

        $existingRecords = $query->get();

        // Check each record for normalized match
        foreach ($existingRecords as $record) {
            $existingName = $record->{$this->column};
            $normalizedExisting = NameNormalizer::normalize($existingName);

            if ($normalizedInput === $normalizedExisting) {
                $fail("A {$this->entityType} with a similar name already exists: \"{$existingName}\". Please choose a different name.");
                return;
            }
        }
    }

    /**
     * Guess the entity type from the table name for error messages.
     */
    private function guessEntityType(string $table): string
    {
        $types = [
            'users' => 'user',
            'organisers' => 'organiser',
            'artists' => 'artist',
            'venues' => 'venue',
        ];

        return $types[$table] ?? str_replace('_', ' ', rtrim($table, 's'));
    }

    /**
     * Static factory method for user names.
     */
    public static function forUser(?int $excludeId = null): self
    {
        return new self('users', 'name', $excludeId, 'user');
    }

    /**
     * Static factory method for organiser names.
     */
    public static function forOrganiser(?int $excludeId = null): self
    {
        return new self('organisers', 'organisation_name', $excludeId, 'organiser');
    }

    /**
     * Static factory method for artist names.
     */
    public static function forArtist(?int $excludeId = null): self
    {
        return new self('artists', 'stage_name', $excludeId, 'artist');
    }

    /**
     * Static factory method for venue names.
     */
    public static function forVenue(?int $excludeId = null): self
    {
        return new self('venues', 'name', $excludeId, 'venue');
    }
}


