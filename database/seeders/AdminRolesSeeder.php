<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laratrust\Models\Permission;
use Laratrust\Models\Role;

class AdminRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrator with full access to the system',
        ]);

        // Create superuser role
        $superuserRole = Role::firstOrCreate([
            'name' => 'superuser',
            'display_name' => 'Super User',
            'description' => 'Super user with complete system access',
        ]);

        // Create public-facing roles
        $artistRole = Role::firstOrCreate([
            'name' => 'artist',
            'display_name' => 'Artist',
            'description' => 'Performer profile with artist capabilities',
        ]);

        $organiserRole = Role::firstOrCreate([
            'name' => 'organiser',
            'display_name' => 'Organiser',
            'description' => 'Event organiser with event management capabilities',
        ]);

        $generalUserRole = Role::firstOrCreate([
            'name' => 'user',
            'display_name' => 'General User',
            'description' => 'General site user with basic access',
        ]);

        // Create admin permissions
        $permissions = [
            'manage-users' => 'Manage Users',
            'manage-events' => 'Manage Events',
            'manage-venues' => 'Manage Venues',
            'manage-artists' => 'Manage Artists',
            'manage-organisers' => 'Manage Organisers',
            'view-analytics' => 'View Analytics',
            'manage-settings' => 'Manage Settings',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate([
                'name' => $name,
                'display_name' => $displayName,
                'description' => "Permission to {$displayName}",
            ]);
        }

        // Assign all permissions to admin role
        $adminRole->syncPermissions(Permission::all());

        // Assign all permissions to superuser role
        $superuserRole->syncPermissions(Permission::all());

        // Create admin user
        $adminUser = \App\Models\User::firstOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'System Administrator',
            'email' => 'admin@mygigguide.com',
            'password' => Hash::make('admin123'),
        ]);

        // Assign admin role to admin user
        if (! $adminUser->hasRole('admin')) {
            $adminUser->addRole('admin');
        }

        // Create superuser
        $superuser = \App\Models\User::firstOrCreate([
            'username' => 'superuser',
        ], [
            'name' => 'Super User',
            'email' => 'superuser@mygigguide.com',
            'password' => Hash::make('superuser123'),
        ]);

        // Assign superuser role
        if (! $superuser->hasRole('superuser')) {
            $superuser->addRole('superuser');
        }

        // Additional Superusers requested
        $thando = \App\Models\User::firstOrCreate([
            'username' => 'thando',
        ], [
            'name' => 'Thando',
            'email' => 'thando@mygigguide.com',
            'password' => Hash::make('Gu3ssWh@t'),
        ]);
        if (! $thando->hasRole('superuser')) {
            $thando->addRole('superuser');
        }

        $dave = \App\Models\User::firstOrCreate([
            'username' => 'dave',
        ], [
            'name' => 'Dave',
            'email' => 'dave@mygigguide.com',
            'password' => Hash::make('Dave123!'),
        ]);
        if (! $dave->hasRole('superuser')) {
            $dave->addRole('superuser');
        }
    }
}
