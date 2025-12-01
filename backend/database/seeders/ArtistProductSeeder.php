<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArtistProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all artists
        $artists = User::where('role', 'artist')->get();
        
        if ($artists->isEmpty()) {
            $this->command->warn('No artists found. Please run ArtistSeeder first.');
            return;
        }

        // Get categories
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run ProductSeeder first.');
            return;
        }

        // Product templates for each artist
        $productTemplates = [
            [
                'name' => 'Character Poster - Series A',
                'price' => 60000,
                'stock' => 15,
                'description' => 'High quality poster featuring a beautiful character design from Series A. Perfect for decorating your room or workspace.',
            ],
            [
                'name' => 'Character Poster - Series B',
                'price' => 65000,
                'stock' => 20,
                'description' => 'Stunning poster with detailed character artwork from Series B. Premium quality print on durable paper.',
            ],
            [
                'name' => 'Action Scene Poster',
                'price' => 70000,
                'stock' => 10,
                'description' => 'Dynamic action scene poster showcasing epic moments. High resolution print with vibrant colors.',
            ],
            [
                'name' => 'Character Portrait - Premium',
                'price' => 80000,
                'stock' => 8,
                'description' => 'Premium quality character portrait poster. Limited edition design with exceptional detail.',
            ],
            [
                'name' => 'Landscape Art Poster',
                'price' => 75000,
                'stock' => 12,
                'description' => 'Beautiful landscape artwork poster. Perfect for adding atmosphere to any room.',
            ],
            [
                'name' => 'Character Collection Set',
                'price' => 90000,
                'stock' => 5,
                'description' => 'Special collection set featuring multiple characters. Exclusive design available in limited quantities.',
            ],
            [
                'name' => 'Minimalist Character Design',
                'price' => 55000,
                'stock' => 25,
                'description' => 'Clean and minimalist character design poster. Modern aesthetic perfect for contemporary spaces.',
            ],
            [
                'name' => 'Detailed Character Study',
                'price' => 85000,
                'stock' => 6,
                'description' => 'Intricate character study poster with exceptional attention to detail. Art collector\'s item.',
            ],
        ];

        $productCount = 0;

        foreach ($artists as $artist) {
            $this->command->info("Creating products for artist: {$artist->name} ({$artist->username})");
            
            // Create 3-5 products per artist
            $productsToCreate = fake()->numberBetween(3, 5);
            
            for ($i = 0; $i < $productsToCreate; $i++) {
                $template = fake()->randomElement($productTemplates);
                $category = fake()->randomElement($categories);
                
                // Add artist name to product name for uniqueness
                $productName = str_replace(['Series A', 'Series B'], [
                    fake()->words(2, true),
                    fake()->words(2, true)
                ], $template['name']);
                
                $productName = "{$productName} by {$artist->name}";
                
                Product::create([
                    'name' => $productName,
                    'description' => $template['description'],
                    'price' => $template['price'] + fake()->numberBetween(-10000, 20000),
                    'stock' => $template['stock'] + fake()->numberBetween(-5, 10),
                    'image' => 'https://picsum.photos/600/800?random=' . ($artist->id * 100 + $i),
                    'category_id' => $category->id,
                    'artist_id' => $artist->id,
                    'clicks' => fake()->numberBetween(0, 1000),
                    'sales_count' => fake()->numberBetween(0, 500),
                ]);
                
                $productCount++;
            }
        }

        $this->command->info("Successfully created {$productCount} products for {$artists->count()} artists!");
    }
}


