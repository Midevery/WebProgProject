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
            'Lenovo ThinkPad X1 Carbon Gen 8',
            'Dell Latitude 5420',
            'HP EliteBook 840 G7',
            'MacBook Air 2017',
            'iPhone 12 - 64GB',
            'Samsung Galaxy S21',
            'iPad Air 4th Gen',
            'Logitech MX Master 3',
            'Mechanical Keyboard RGB',
            'External SSD 500GB',
        ];

        $stock = fake()->randomElement([0, 10, 20, 30, 50, 100]);
        
        return [
            'name' => fake()->randomElement($names),
            'description' => fake()->paragraph(3),
            'price' => fake()->numberBetween(500000, 12000000),
            'cost' => fake()->numberBetween(300000, 8000000),
            'stock' => $stock,
            'image' => 'https://picsum.photos/600/800?random=' . fake()->numberBetween(1, 1000),
            'category_id' => Category::factory(),
            'seller_id' => User::factory()->seller(),
            'clicks' => fake()->numberBetween(0, 1000),
            'sales_count' => fake()->numberBetween(0, 500),
        ];
    }
}
