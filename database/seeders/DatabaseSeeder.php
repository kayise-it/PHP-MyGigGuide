<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laratrust\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles, permissions and sample data
        $this->call([
            LaratrustSeeder::class,
            GenreSeeder::class,
            CategorySeeder::class,
            PaidFeaturesSeeder::class,
            FeatureProgramsSeeder::class,
            SampleDataSeeder::class,
        ]);

        // Ensure there is at least one superuser for admin access
        if (! User::where('email', 'admin@example.com')->exists()) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);

            if ($role = Role::where('name', 'superuser')->first()) {
                $adminUser->attachRole($role);
            }
        }
    }
}
