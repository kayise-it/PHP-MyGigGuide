<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laratrust\Models\Permission;
use Laratrust\Models\Role;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superuser = Role::firstOrCreate(
            ['name' => 'superuser'],
            [
                'display_name' => 'Super User',
                'description' => 'System owner with ultimate privileges',
            ]
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'System administrator with full access',
            ]
        );

        $artist = Role::firstOrCreate(
            ['name' => 'artist'],
            [
                'display_name' => 'Artist',
                'description' => 'User who creates and manages artist profiles',
            ]
        );

        $organiser = Role::firstOrCreate(
            ['name' => 'organiser'],
            [
                'display_name' => 'Event Organiser',
                'description' => 'User who creates and manages events and venues',
            ]
        );

        $venueOwner = Role::firstOrCreate(
            ['name' => 'venue_owner'],
            [
                'display_name' => 'Venue Owner',
                'description' => 'User who manages venue information',
            ]
        );

        $user = Role::firstOrCreate(
            ['name' => 'user'],
            [
                'display_name' => 'Regular User',
                'description' => 'Basic authenticated user',
            ]
        );

        // Create permissions
        $permissions = [
            // User management
            ['name' => 'manage-users', 'display_name' => 'Manage Users', 'description' => 'Can manage all users'],
            ['name' => 'view-users', 'display_name' => 'View Users', 'description' => 'Can view user profiles'],
            ['name' => 'edit-profile', 'display_name' => 'Edit Profile', 'description' => 'Can edit own profile'],

            // Event management
            ['name' => 'create-events', 'display_name' => 'Create Events', 'description' => 'Can create events'],
            ['name' => 'edit-events', 'display_name' => 'Edit Events', 'description' => 'Can edit events'],
            ['name' => 'delete-events', 'display_name' => 'Delete Events', 'description' => 'Can delete events'],
            ['name' => 'view-events', 'display_name' => 'View Events', 'description' => 'Can view events'],

            // Artist management
            ['name' => 'create-artist-profile', 'display_name' => 'Create Artist Profile', 'description' => 'Can create artist profile'],
            ['name' => 'edit-artist-profile', 'display_name' => 'Edit Artist Profile', 'description' => 'Can edit artist profile'],
            ['name' => 'view-artists', 'display_name' => 'View Artists', 'description' => 'Can view artist profiles'],

            // Venue management
            ['name' => 'create-venues', 'display_name' => 'Create Venues', 'description' => 'Can create venues'],
            ['name' => 'edit-venues', 'display_name' => 'Edit Venues', 'description' => 'Can edit venues'],
            ['name' => 'delete-venues', 'display_name' => 'Delete Venues', 'description' => 'Can delete venues'],
            ['name' => 'view-venues', 'display_name' => 'View Venues', 'description' => 'Can view venues'],
            ['name' => 'claim-venues', 'display_name' => 'Claim Venues', 'description' => 'Can claim venue ownership'],

            // Organiser management
            ['name' => 'create-organiser-profile', 'display_name' => 'Create Organiser Profile', 'description' => 'Can create organiser profile'],
            ['name' => 'edit-organiser-profile', 'display_name' => 'Edit Organiser Profile', 'description' => 'Can edit organiser profile'],
            ['name' => 'view-organisers', 'display_name' => 'View Organisers', 'description' => 'Can view organiser profiles'],

            // Rating and review
            ['name' => 'rate-content', 'display_name' => 'Rate Content', 'description' => 'Can rate and review content'],
            ['name' => 'view-ratings', 'display_name' => 'View Ratings', 'description' => 'Can view ratings and reviews'],

            // Admin permissions
            ['name' => 'moderate-content', 'display_name' => 'Moderate Content', 'description' => 'Can moderate all content'],
            ['name' => 'view-analytics', 'display_name' => 'View Analytics', 'description' => 'Can view system analytics'],
            ['name' => 'manage-system', 'display_name' => 'Manage System', 'description' => 'Can manage system settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to roles
        $superuser->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'manage-users', 'view-users', 'create-events', 'edit-events', 'delete-events', 'view-events',
            'view-artists', 'create-venues', 'edit-venues', 'delete-venues', 'view-venues',
            'view-organisers', 'view-ratings', 'moderate-content', 'view-analytics',
        ]);

        $artist->syncPermissions([
            'edit-profile', 'create-events', 'edit-events', 'delete-events', 'view-events',
            'create-artist-profile', 'edit-artist-profile', 'view-artists',
            'create-venues', 'edit-venues', 'view-venues', 'claim-venues',
            'rate-content', 'view-ratings',
        ]);

        $organiser->syncPermissions([
            'edit-profile', 'create-events', 'edit-events', 'delete-events', 'view-events',
            'create-organiser-profile', 'edit-organiser-profile', 'view-organisers',
            'create-venues', 'edit-venues', 'view-venues', 'claim-venues',
            'rate-content', 'view-ratings',
        ]);

        $venueOwner->syncPermissions([
            'edit-profile', 'edit-venues', 'view-venues', 'rate-content', 'view-ratings',
        ]);

        $user->syncPermissions([
            'edit-profile', 'view-events', 'view-artists', 'view-venues', 'view-organisers',
            'rate-content', 'view-ratings',
        ]);
    }
}
