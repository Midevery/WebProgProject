<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $names = [
            'Lumine - Genshin Impact',
            'Changli - Wuthering Waves',
            'Jinshi - Apothecary Diaries',
            'Male Rover - Wuthering Waves',
            'Canthelya - Wuthering Waves',
            'Iuno - Wuthering Waves',
            'Juno - Wuthering Waves',
            'Earthelys - Wuthering Waves',
            'Raiden Shogun - Genshin Impact',
            'Nahida - Genshin Impact',
            'Furina - Genshin Impact',
            'Neuvillette - Genshin Impact',
            'Aether - Genshin Impact',
            'Zhongli - Genshin Impact',
            'Venti - Genshin Impact',
        ];

        $stock = fake()->randomElement([0, 10, 20, 30, 50, 100]);
        
        return [
            'name' => fake()->randomElement($names),
            'description' => fake()->paragraph(3),
            'price' => fake()->numberBetween(45000, 150000),
            'stock' => $stock,
            'image' => 'https://picsum.photos/600/800?random=' . fake()->numberBetween(1, 1000),
            'category_id' => Category::factory(),
            'artist_id' => User::factory(),
            'clicks' => fake()->numberBetween(0, 1000),
            'sales_count' => fake()->numberBetween(0, 500),
        ];
    }
}
