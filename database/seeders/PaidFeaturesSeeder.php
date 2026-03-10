<?php

namespace Database\Seeders;

use App\Models\PaidFeature;
use Illuminate\Database\Seeder;

class PaidFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            [
                'name' => 'Homepage Spotlight',
                'applies_to' => 'artist',
                'description' => 'Feature the artist on the homepage spotlight area.',
                'duration_days' => 7,
                'price_cents' => 19900,
                'currency' => 'ZAR',
                'is_active' => true,
                'settings' => ['weight' => 20, 'badge' => 'Spotlight'],
            ],
            [
                'name' => 'Top of Venue Listings',
                'applies_to' => 'venue',
                'description' => 'Pin the venue to the top of venue search results.',
                'duration_days' => 14,
                'price_cents' => 14900,
                'currency' => 'ZAR',
                'is_active' => true,
                'settings' => ['weight' => 15, 'badge' => 'Featured'],
            ],
            [
                'name' => 'Event Boost',
                'applies_to' => 'event',
                'description' => 'Boost event visibility and highlight in event feeds.',
                'duration_days' => 7,
                'price_cents' => 9900,
                'currency' => 'ZAR',
                'is_active' => true,
                'settings' => ['weight' => 10, 'badge' => 'Boosted'],
            ],
        ];

        foreach ($features as $data) {
            PaidFeature::updateOrCreate([
                'name' => $data['name'],
                'applies_to' => $data['applies_to'],
            ], $data);
        }
    }
}









