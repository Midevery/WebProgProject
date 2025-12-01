<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default password for all artists
        $defaultPassword = 'AAAaaa123';
        
        // Also create/update admin user
        $existingAdmin = User::where('email', 'admin@kisora.com')
            ->orWhere('username', 'admin')
            ->first();
        
        if (!$existingAdmin) {
            User::create([
                'username' => 'admin',
                'name' => 'Admin',
                'email' => 'admin@kisora.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '08123456789',
            ]);
            $this->command->info("Created admin user");
        } else {
            $existingAdmin->update([
                'password' => Hash::make('admin123'),
            ]);
            $this->command->info("Updated password for existing admin user");
        }

        // List of artists to create
        $artists = [
            ['username' => 'joma', 'name' => 'Joma', 'email' => 'joma@kisora.com', 'role' => 'artist'],
            ['username' => 'bobo', 'name' => 'Bobo', 'email' => 'bobo@kisora.com', 'role' => 'artist'],
            ['username' => 'stevenhe', 'name' => 'Steven he', 'email' => 'stevenhe@kisora.com', 'role' => 'artist'],
            // Additional artists
            ['username' => 'sakura_art', 'name' => 'Sakura Art', 'email' => 'sakura@kisora.com', 'role' => 'artist'],
            ['username' => 'moonlight_illust', 'name' => 'Moonlight Illustrator', 'email' => 'moonlight@kisora.com', 'role' => 'artist'],
            ['username' => 'star_drawer', 'name' => 'Star Drawer', 'email' => 'star@kisora.com', 'role' => 'artist'],
            ['username' => 'anime_master', 'name' => 'Anime Master', 'email' => 'anime@kisora.com', 'role' => 'artist'],
            ['username' => 'digital_artist', 'name' => 'Digital Artist', 'email' => 'digital@kisora.com', 'role' => 'artist'],
            ['username' => 'manga_creator', 'name' => 'Manga Creator', 'email' => 'manga@kisora.com', 'role' => 'artist'],
            ['username' => 'art_studio', 'name' => 'Art Studio', 'email' => 'studio@kisora.com', 'role' => 'artist'],
        ];

        foreach ($artists as $artistData) {
            // Check if artist already exists
            $existingArtist = User::where('email', $artistData['email'])
                ->orWhere('username', $artistData['username'])
                ->first();

            if (!$existingArtist) {
                User::create(array_merge($artistData, [
                    'password' => Hash::make($defaultPassword),
                    'phone' => '08123456789',
                    'address' => 'Artist Studio',
                    'balance' => 500000,
                    'gender' => fake()->randomElement(['Male', 'Female']),
                    'date_of_birth' => fake()->date('Y-m-d', '-25 years'),
                ]));
                $this->command->info("Created artist: {$artistData['name']} ({$artistData['username']})");
            } else {
                // Update existing artist password
                $existingArtist->update([
                    'password' => Hash::make($defaultPassword),
                ]);
                $this->command->info("Updated password for existing artist: {$artistData['name']} ({$artistData['username']})");
            }
        }

        $this->command->info('Artist seeding completed!');
        $this->command->info("Default password for all artists: {$defaultPassword}");
    }
}

