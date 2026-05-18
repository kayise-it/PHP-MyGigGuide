<?php

use Illuminate\Database\Migrations\Migration;
use Laratrust\Models\Permission;
use Laratrust\Models\Role;

/**
 * Site UI shows "List an Event" to all @auth users; grant matching Laratrust permissions
 * on existing installs (seeder alone does not re-run in production).
 */
return new class extends Migration
{
    private const EVENT_PERMISSIONS = [
        'create-events',
        'edit-events',
        'delete-events',
        'view-events',
    ];

    private const ROLE_NAMES = ['user', 'venue_owner', 'superuser', 'admin'];

    public function up(): void
    {
        if (! class_exists(Role::class) || ! class_exists(Permission::class)) {
            return;
        }

        $permissions = Permission::query()
            ->whereIn('name', self::EVENT_PERMISSIONS)
            ->get()
            ->keyBy('name');

        foreach (self::ROLE_NAMES as $roleName) {
            $role = Role::query()->where('name', $roleName)->first();
            if (! $role) {
                continue;
            }
            foreach (self::EVENT_PERMISSIONS as $permName) {
                $permission = $permissions->get($permName);
                if ($permission && ! $role->hasPermission($permName)) {
                    $role->givePermission($permission);
                }
            }
        }
    }

    public function down(): void
    {
        if (! class_exists(Role::class)) {
            return;
        }

        foreach (self::ROLE_NAMES as $roleName) {
            $role = Role::query()->where('name', $roleName)->first();
            if (! $role) {
                continue;
            }
            foreach (self::EVENT_PERMISSIONS as $permName) {
                if ($role->hasPermission($permName)) {
                    $role->removePermission($permName);
                }
            }
        }

        // Restore venue_owner / user to pre-migration event access (view-events only for user).
        $user = Role::query()->where('name', 'user')->first();
        if ($user && ! $user->hasPermission('view-events')) {
            $view = Permission::query()->where('name', 'view-events')->first();
            if ($view) {
                $user->givePermission($view);
            }
        }
    }
};
