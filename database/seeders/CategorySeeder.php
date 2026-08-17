<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'Live music', 'slug' => 'live-music', 'sort_order' => 10],
            ['name' => 'DJ / Club', 'slug' => 'dj-club', 'sort_order' => 20],
            ['name' => 'Festival', 'slug' => 'festival', 'sort_order' => 30],
            ['name' => 'Comedy', 'slug' => 'comedy', 'sort_order' => 40],
            ['name' => 'Theatre', 'slug' => 'theatre', 'sort_order' => 50],
            ['name' => 'Open mic', 'slug' => 'open-mic', 'sort_order' => 60],
            ['name' => 'Sports', 'slug' => 'sports', 'sort_order' => 70],
            ['name' => 'Travel & outdoor', 'slug' => 'travel-outdoor', 'sort_order' => 80],
            ['name' => 'Family Friendly', 'slug' => 'family-friendly', 'sort_order' => 85],
            // Import source tag (also create on production via admin if missing)
            ['name' => 'Quicket', 'slug' => 'quicket', 'sort_order' => 90],
        ];

        foreach ($rows as $row) {
            Category::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}
