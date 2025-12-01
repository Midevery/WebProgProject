<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $plushie = Category::create(['name' => 'Plushie', 'slug' => 'plushie', 'description' => 'Soft and cuddly plush toys']);
        $poster = Category::create(['name' => 'Poster', 'slug' => 'poster', 'description' => 'High quality anime posters']);
        $nendoroid = Category::create(['name' => 'Nendoroid', 'slug' => 'nendoroid', 'description' => 'Chibi-style collectible figures']);
        $figure = Category::create(['name' => 'Figure', 'slug' => 'figure', 'description' => 'Detailed anime figures']);
        $merchandise = Category::create(['name' => 'Merchandise', 'slug' => 'merchandise', 'description' => 'Various anime merchandise']);

        // Create Artists
        $artists = [
            ['username' => 'joma', 'name' => 'Joma', 'email' => 'joma@kisora.com', 'role' => 'artist'],
            ['username' => 'bobo', 'name' => 'Bobo', 'email' => 'bobo@kisora.com', 'role' => 'artist'],
            ['username' => 'stevenhe', 'name' => 'Steven he', 'email' => 'stevenhe@kisora.com', 'role' => 'artist'],
        ];

        foreach ($artists as $artistData) {
            // Check if artist already exists
            $existingArtist = User::where('email', $artistData['email'])
                ->orWhere('username', $artistData['username'])
                ->first();

            if (!$existingArtist) {
                User::create(array_merge($artistData, [
                    'password' => bcrypt('AAAaaa123'),
                    'phone' => '08123456789',
                    'address' => 'Artist Studio',
                    'balance' => 500000,
                    'gender' => fake()->randomElement(['Male', 'Female']),
                    'date_of_birth' => fake()->date('Y-m-d', '-25 years'),
                ]));
            } else {
                // Update existing artist password
                $existingArtist->update([
                    'password' => bcrypt('AAAaaa123'),
                ]);
            }
        }

        // Create Admin
        $existingAdmin = User::where('email', 'admin@kisora.com')
            ->orWhere('username', 'admin')
            ->first();
        
        if (!$existingAdmin) {
            User::create([
                'username' => 'admin',
                'name' => 'Admin',
                'email' => 'admin@kisora.com',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'phone' => '08123456789',
            ]);
        } else {
            $existingAdmin->update([
                'password' => bcrypt('admin123'),
            ]);
        }

        // Get artists
        $artistIds = User::where('role', 'artist')->pluck('id')->toArray();

        // Create Products
        $products = [
            // Genshin Impact Posters
            ['name' => 'Lumine - Genshin Impact', 'price' => 50000, 'stock' => 0, 'category' => $poster],
            ['name' => 'Raiden Shogun - Genshin Impact', 'price' => 75000, 'stock' => 15, 'category' => $poster],
            ['name' => 'Nahida - Genshin Impact', 'price' => 68000, 'stock' => 20, 'category' => $poster],
            ['name' => 'Furina - Genshin Impact', 'price' => 72000, 'stock' => 10, 'category' => $poster],
            ['name' => 'Neuvillette - Genshin Impact', 'price' => 78000, 'stock' => 5, 'category' => $poster],
            ['name' => 'Aether - Genshin Impact', 'price' => 50000, 'stock' => 0, 'category' => $poster],
            
            // Genshin Impact Figures
            ['name' => 'Zhongli - Genshin Impact', 'price' => 85000, 'stock' => 12, 'category' => $figure],
            ['name' => 'Venti - Genshin Impact', 'price' => 80000, 'stock' => 8, 'category' => $figure],
            
            // Wuthering Waves Posters
            ['name' => 'Changli - Wuthering Waves', 'price' => 100000, 'stock' => 25, 'category' => $poster],
            ['name' => 'Male Rover - Wuthering Waves', 'price' => 55000, 'stock' => 0, 'category' => $poster],
            ['name' => 'Canthelya - Wuthering Waves', 'price' => 60000, 'stock' => 18, 'category' => $poster],
            ['name' => 'Iuno - Wuthering Waves', 'price' => 80000, 'stock' => 15, 'category' => $poster],
            ['name' => 'Juno - Wuthering Waves', 'price' => 80000, 'stock' => 20, 'category' => $poster],
            ['name' => 'Earthelys - Wuthering Waves', 'price' => 70000, 'stock' => 0, 'category' => $poster],
            
            // Apothecary Diaries
            ['name' => 'Jinshi - Apothecary Diaries', 'price' => 78000, 'stock' => 0, 'category' => $poster],
            
            // Plushies
            ['name' => 'Totoro Plushie', 'price' => 120000, 'stock' => 30, 'category' => $plushie],
            ['name' => 'Capybara Plushie', 'price' => 95000, 'stock' => 25, 'category' => $plushie],
            
            // Nendoroids
            ['name' => 'Hatsune Miku Nendoroid', 'price' => 150000, 'stock' => 10, 'category' => $nendoroid],
            ['name' => 'Rem Nendoroid', 'price' => 145000, 'stock' => 8, 'category' => $nendoroid],
            
            // Figures
            ['name' => 'Saber Figure', 'price' => 200000, 'stock' => 5, 'category' => $figure],
            ['name' => 'Asuna Figure', 'price' => 180000, 'stock' => 7, 'category' => $figure],
        ];

        foreach ($products as $index => $productData) {
            Product::create([
                'name' => $productData['name'],
                'description' => 'Beautiful artwork of ' . $productData['name'] . '. High quality print on premium paper.',
                'price' => $productData['price'],
                'stock' => $productData['stock'],
                'image' => 'https://picsum.photos/600/800?random=' . ($index + 100),
                'category_id' => $productData['category']->id,
                'artist_id' => fake()->randomElement($artistIds),
                'clicks' => fake()->numberBetween(0, 1000),
                'sales_count' => fake()->numberBetween(0, 500),
            ]);
        }
    }
}
