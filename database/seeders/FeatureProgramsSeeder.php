<?php

namespace Database\Seeders;

use App\Models\FeatureProgram;
use Illuminate\Database\Seeder;

class FeatureProgramsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Artist Program', 'slug' => 'artist-program', 'applies_to' => 'artist', 'is_active' => true],
            ['name' => 'Venue Program', 'slug' => 'venue-program', 'applies_to' => 'venue', 'is_active' => true],
            ['name' => 'Event Program', 'slug' => 'event-program', 'applies_to' => 'event', 'is_active' => true],
        ];

        foreach ($items as $data) {
            FeatureProgram::updateOrCreate([
                'slug' => $data['slug'],
            ], $data);
        }
    }
}









