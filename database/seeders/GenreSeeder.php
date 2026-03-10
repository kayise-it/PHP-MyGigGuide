<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            [
                'name' => 'Rock',
                'description' => 'Rock music is a broad genre of popular music that originated in the 1950s, characterized by a strong beat, simple chord structure, and often electric guitars.',
            ],
            [
                'name' => 'Jazz',
                'description' => 'Jazz is a music genre characterized by swing and blue notes, complex chords, call and response vocals, polyrhythms and improvisation.',
            ],
            [
                'name' => 'Hip Hop',
                'description' => 'Hip hop music is a genre of popular music that originated in the 1970s, consisting of stylized rhythmic music that commonly accompanies rapping.',
            ],
            [
                'name' => 'Electronic',
                'description' => 'Electronic music is music that employs electronic musical instruments, digital instruments, or circuitry-based music technology in its creation.',
            ],
            [
                'name' => 'Pop',
                'description' => 'Pop music is a genre of popular music that originated in its modern form during the mid-1950s, characterized by catchy melodies and simple chord progressions.',
            ],
            [
                'name' => 'R&B',
                'description' => 'Rhythm and blues (R&B) is a genre of popular music that originated in African American communities in the 1940s, combining elements of jazz, gospel, and blues.',
            ],
            [
                'name' => 'Country',
                'description' => 'Country music is a genre of popular music that originated in the Southern United States in the 1920s, characterized by simple harmonies and folk lyrics.',
            ],
            [
                'name' => 'Reggae',
                'description' => 'Reggae is a music genre that originated in Jamaica in the late 1960s, characterized by a strong backbeat and often socially conscious lyrics.',
            ],
            [
                'name' => 'Blues',
                'description' => 'Blues is a music genre and musical form that originated in the Deep South of the United States around the 1860s, characterized by blue notes and a twelve-bar structure.',
            ],
            [
                'name' => 'Classical',
                'description' => 'Classical music generally refers to the formal musical tradition of the Western world, considered to be distinct from Western folk music or popular music traditions.',
            ],
            [
                'name' => 'Metal',
                'description' => 'Heavy metal is a genre of rock music that developed in the late 1960s and early 1970s, characterized by highly amplified distortion and powerful vocals.',
            ],
            [
                'name' => 'Folk',
                'description' => 'Folk music is a music genre that includes traditional folk music and the contemporary genre that evolved from the former during the 20th-century folk revival.',
            ],
            [
                'name' => 'Soul',
                'description' => 'Soul music is a popular music genre that originated in the African American community, combining elements of gospel music and rhythm and blues.',
            ],
            [
                'name' => 'Indie',
                'description' => 'Indie music is music produced independently from commercial record labels, often characterized by experimental sounds and artistic freedom.',
            ],
            [
                'name' => 'Gospel',
                'description' => 'Gospel music is a traditional genre of Christian music, characterized by dominant vocals and strong use of harmony with Christian lyrics.',
            ],
        ];

        foreach ($genres as $genre) {
            Genre::create([
                'name' => $genre['name'],
                'slug' => Str::slug($genre['name']),
                'description' => $genre['description'],
                'is_active' => true,
            ]);
        }
    }
}
