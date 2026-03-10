<?php

namespace App\Services;

use App\Helpers\NameNormalizer;
use App\Models\Artist;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Collection;

class DuplicateNameService
{
    /**
     * Entity configurations for duplicate scanning.
     */
    protected array $entityConfigs = [
        'users' => [
            'model' => User::class,
            'name_column' => 'name',
            'label' => 'User',
            'identifier_column' => 'email',
        ],
        'organisers' => [
            'model' => Organiser::class,
            'name_column' => 'organisation_name',
            'label' => 'Organiser',
            'identifier_column' => 'contact_email',
        ],
        'artists' => [
            'model' => Artist::class,
            'name_column' => 'stage_name',
            'label' => 'Artist',
            'identifier_column' => 'contact_email',
        ],
        'venues' => [
            'model' => Venue::class,
            'name_column' => 'name',
            'label' => 'Venue',
            'identifier_column' => 'contact_email',
        ],
    ];

    /**
     * Find all duplicates across all entity types.
     *
     * @return array
     */
    public function findAllDuplicates(): array
    {
        $allDuplicates = [];

        foreach ($this->entityConfigs as $type => $config) {
            $duplicates = $this->findDuplicatesForEntity($type);
            if (!empty($duplicates)) {
                $allDuplicates[$type] = [
                    'label' => $config['label'],
                    'groups' => $duplicates,
                    'total_duplicates' => collect($duplicates)->sum(fn($group) => count($group['items'])),
                ];
            }
        }

        return $allDuplicates;
    }

    /**
     * Find duplicates for a specific entity type.
     *
     * @param string $entityType
     * @return array
     */
    public function findDuplicatesForEntity(string $entityType): array
    {
        if (!isset($this->entityConfigs[$entityType])) {
            throw new \InvalidArgumentException("Unknown entity type: {$entityType}");
        }

        $config = $this->entityConfigs[$entityType];
        $model = $config['model'];
        $nameColumn = $config['name_column'];
        $identifierColumn = $config['identifier_column'];

        // Get all records with their names
        $records = $model::select(['id', $nameColumn, $identifierColumn, 'created_at', 'updated_at'])
            ->get()
            ->map(function ($record) use ($nameColumn, $identifierColumn) {
                return [
                    'id' => $record->id,
                    'name' => $record->{$nameColumn},
                    'normalized_name' => NameNormalizer::normalize($record->{$nameColumn}),
                    'identifier' => $record->{$identifierColumn},
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ];
            });

        // Group by normalized name
        $grouped = $records->groupBy('normalized_name');

        // Filter to only groups with duplicates (more than 1 record)
        $duplicates = $grouped->filter(fn($group) => $group->count() > 1);

        // Format the output
        return $duplicates->map(function ($group, $normalizedName) {
            return [
                'normalized_name' => $normalizedName,
                'items' => $group->values()->toArray(),
            ];
        })->values()->toArray();
    }

    /**
     * Get duplicate count summary.
     *
     * @return array
     */
    public function getDuplicateSummary(): array
    {
        $summary = [];

        foreach ($this->entityConfigs as $type => $config) {
            $duplicates = $this->findDuplicatesForEntity($type);
            $totalDuplicates = collect($duplicates)->sum(fn($group) => count($group['items']));
            $groupCount = count($duplicates);

            $summary[$type] = [
                'label' => $config['label'],
                'duplicate_groups' => $groupCount,
                'total_records_affected' => $totalDuplicates,
            ];
        }

        return $summary;
    }

    /**
     * Get detailed information about a specific entity by ID.
     *
     * @param string $entityType
     * @param int $id
     * @return array|null
     */
    public function getEntityDetails(string $entityType, int $id): ?array
    {
        if (!isset($this->entityConfigs[$entityType])) {
            return null;
        }

        $config = $this->entityConfigs[$entityType];
        $model = $config['model'];

        $record = $model::find($id);

        if (!$record) {
            return null;
        }

        $details = [
            'id' => $record->id,
            'type' => $entityType,
            'label' => $config['label'],
            'name' => $record->{$config['name_column']},
            'normalized_name' => NameNormalizer::normalize($record->{$config['name_column']}),
            'identifier' => $record->{$config['identifier_column']},
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ];

        // Add type-specific details
        switch ($entityType) {
            case 'users':
                $details['username'] = $record->username;
                $details['email_verified'] = $record->hasVerifiedEmail();
                $details['roles'] = $record->roles()->pluck('name')->toArray();
                break;

            case 'artists':
                $details['user_id'] = $record->user_id;
                $details['user_name'] = $record->user?->name;
                $details['genre'] = $record->genre;
                break;

            case 'organisers':
                $details['user_id'] = $record->user_id;
                $details['user_name'] = $record->user?->name;
                break;

            case 'venues':
                $details['address'] = $record->address;
                $details['city'] = $record->location ?? $record->city;
                break;
        }

        return $details;
    }

    /**
     * Rename an entity's name field.
     *
     * @param string $entityType
     * @param int $id
     * @param string $newName
     * @return bool
     * @throws \Exception
     */
    public function renameEntity(string $entityType, int $id, string $newName): bool
    {
        if (!isset($this->entityConfigs[$entityType])) {
            throw new \InvalidArgumentException("Unknown entity type: {$entityType}");
        }

        $config = $this->entityConfigs[$entityType];
        $model = $config['model'];
        $nameColumn = $config['name_column'];

        // Check if new name would create a duplicate
        $normalizedNew = NameNormalizer::normalize($newName);
        
        $existingWithSameName = $model::where('id', '!=', $id)
            ->get()
            ->filter(fn($record) => NameNormalizer::normalize($record->{$nameColumn}) === $normalizedNew);

        if ($existingWithSameName->count() > 0) {
            throw new \Exception("The new name '{$newName}' would create another duplicate.");
        }

        $record = $model::findOrFail($id);
        $record->{$nameColumn} = $newName;
        $record->save();

        return true;
    }

    /**
     * Get available entity types.
     *
     * @return array
     */
    public function getEntityTypes(): array
    {
        return array_map(fn($config) => [
            'value' => $config['label'],
            'name_column' => $config['name_column'],
        ], $this->entityConfigs);
    }
}


