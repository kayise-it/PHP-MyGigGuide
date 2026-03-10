<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Artist;
use App\Models\Venue;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Rating;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users with different roles
        $superuser = User::firstOrCreate(
            ['email' => 'admin@mygigguide.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'username' => 'superadmin',
            ]
        );
        if (! $superuser->hasRole('superuser')) {
            $superuser->addRole('superuser');
        }

        $artistUser = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'username' => 'johndoe',
            ]
        );
        if (! $artistUser->hasRole('artist')) {
            $artistUser->addRole('artist');
        }

        $organiserUser = User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password'),
                'username' => 'janesmith',
            ]
        );
        if (! $organiserUser->hasRole('organiser')) {
            $organiserUser->addRole('organiser');
        }

        $regularUser = User::firstOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Wilson',
                'password' => Hash::make('password'),
                'username' => 'bobwilson',
            ]
        );
        if (! $regularUser->hasRole('user')) {
            $regularUser->addRole('user');
        }

        // Create artist profile
        $artist = Artist::create([
            'user_id' => $artistUser->id,
            'stage_name' => 'Johnny Rock',
            'real_name' => 'John Doe',
            'genre' => 'Rock',
            'bio' => 'A passionate rock musician with over 10 years of experience performing live shows.',
            'phone_number' => '+1234567890',
            'instagram' => '@johnnyrock',
            'facebook' => 'johnnyrockmusic',
            'twitter' => '@johnnyrock',
        ]);

        // Create organiser profile
        $organiser = Organiser::create([
            'user_id' => $organiserUser->id,
            'organisation_name' => 'Music Events Co.',
            'contact_email' => 'events@musicevents.com',
            'phone_number' => '+1234567891',
            'website' => 'https://musicevents.com',
            'description' => 'We organize amazing music events and concerts.',
        ]);

        // Create venues
        $venue1 = Venue::create([
            'name' => 'The Grand Theater',
            'location' => 'Downtown',
            'capacity' => 500,
            'contact_email' => 'info@grandtheater.com',
            'phone_number' => '+1234567892',
            'website' => 'https://grandtheater.com',
            'address' => '123 Main Street, Downtown',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'user_id' => $organiserUser->id,
            'owner_id' => $organiser->id,
            'owner_type' => 'organiser',
        ]);

        $venue2 = Venue::create([
            'name' => 'Rock Arena',
            'location' => 'Music District',
            'capacity' => 1000,
            'contact_email' => 'bookings@rockarena.com',
            'phone_number' => '+1234567893',
            'website' => 'https://rockarena.com',
            'address' => '456 Music Lane, Music District',
            'latitude' => 40.7589,
            'longitude' => -73.9851,
            'user_id' => $artistUser->id,
            'owner_id' => $artist->id,
            'owner_type' => 'artist',
        ]);

        // Create events
        $event1 = Event::create([
            'name' => 'Rock Night Live',
            'description' => 'An amazing night of rock music featuring the best local and international artists.',
            'date' => now()->addDays(30),
            'time' => '20:00:00',
            'price' => 25.00,
            'ticket_url' => 'https://tickets.example.com/rock-night',
            'category' => 'Rock',
            'capacity' => 500,
            'venue_id' => $venue1->id,
            'owner_id' => $organiser->id,
            'owner_type' => 'organiser',
        ]);

        $event2 = Event::create([
            'name' => 'Summer Music Festival',
            'description' => 'A three-day music festival featuring various genres and artists.',
            'date' => now()->addDays(45),
            'time' => '18:00:00',
            'price' => 75.00,
            'ticket_url' => 'https://tickets.example.com/summer-fest',
            'category' => 'Festival',
            'capacity' => 1000,
            'venue_id' => $venue2->id,
            'owner_id' => $artist->id,
            'owner_type' => 'artist',
        ]);

        $event3 = Event::create([
            'name' => 'Acoustic Sessions',
            'description' => 'Intimate acoustic performances in a cozy setting.',
            'date' => now()->addDays(15),
            'time' => '19:30:00',
            'price' => 15.00,
            'category' => 'Acoustic',
            'capacity' => 100,
            'venue_id' => $venue1->id,
            'owner_id' => $organiser->id,
            'owner_type' => 'organiser',
        ]);

        // Attach artist to events
        $event1->artists()->attach($artist->id);
        $event2->artists()->attach($artist->id);

        // Create some ratings
        Rating::create([
            'user_id' => $regularUser->id,
            'rateable_id' => $event1->id,
            'rateable_type' => 'event',
            'rating' => 5,
            'review' => 'Amazing event! Great music and atmosphere.',
        ]);

        Rating::create([
            'user_id' => $regularUser->id,
            'rateable_id' => $artist->id,
            'rateable_type' => 'artist',
            'rating' => 5,
            'review' => 'Johnny Rock is an incredible performer!',
        ]);

        Rating::create([
            'user_id' => $regularUser->id,
            'rateable_id' => $venue1->id,
            'rateable_type' => 'venue',
            'rating' => 4,
            'review' => 'Great venue with excellent sound system.',
        ]);
    }
}
