<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserAlreadyHasEntityException extends Exception
{
    public string $entityType;
    public Model $existingEntity;
    public User $user;

    public function __construct(string $entityType, Model $existingEntity, User $user)
    {
        $this->entityType = $entityType;
        $this->existingEntity = $existingEntity;
        $this->user = $user;

        $entityName = $existingEntity->getDisplayName();
        
        parent::__construct(
            "User {$user->name} already has a {$entityType} profile: {$entityName}"
        );
    }

    /**
     * Get data for the conflict modal.
     */
    public function getConflictData(): array
    {
        return [
            'type' => $this->entityType,
            'existing_id' => $this->existingEntity->id,
            'existing_name' => $this->existingEntity->getDisplayName(),
            'user_name' => $this->user->name,
            'user_id' => $this->user->id,
        ];
    }
}


