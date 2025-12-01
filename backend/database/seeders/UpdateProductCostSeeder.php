<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProductCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $categories = Category::all()->keyBy('name');
        
        // Cost calculation rules based on category and price
        $costRules = [
            'Nendoroid' => [
                'base_cost' => 5000, // Base cost for nendoroid
                'percentage' => 0.12, // 12% of price for nendoroid (since they're expensive)
            ],
            'Figure' => [
                'base_cost' => 10000, // Base cost for figures
                'percentage' => 0.15, // 15% of price for figures
            ],
            'Plushie' => [
                'base_cost' => 8000, // Base cost for plushies
                'percentage' => 0.13, // 13% of price for plushies
            ],
            'Poster' => [
                'base_cost' => 5000, // Base cost for posters
                'percentage' => 0.10, // 10% of price for posters
            ],
            'Merchandise' => [
                'base_cost' => 5000, // Base cost for merchandise
                'percentage' => 0.12, // 12% of price for merchandise
            ],
        ];

        // Update all products
        $products = Product::with('category')->get();
        
        $updated = 0;
        
        foreach ($products as $product) {
            $categoryName = $product->category->name ?? 'Poster';
            $price = $product->price;
            
            // Get cost rule for this category, default to Poster if not found
            $rule = $costRules[$categoryName] ?? $costRules['Poster'];
            
            // Calculate cost: base_cost + (price * percentage)
            // But ensure minimum cost based on price range
            $calculatedCost = $rule['base_cost'] + ($price * $rule['percentage']);
            
            // Additional logic: higher price = higher cost
            // For very expensive items (>150k), add extra cost
            if ($price > 150000) {
                $calculatedCost = max($calculatedCost, $price * 0.18); // At least 18% for expensive items
            } elseif ($price > 100000) {
                $calculatedCost = max($calculatedCost, $price * 0.15); // At least 15% for mid-high items
            } elseif ($price > 50000) {
                $calculatedCost = max($calculatedCost, $price * 0.12); // At least 12% for mid items
            } else {
                $calculatedCost = max($calculatedCost, $price * 0.10); // At least 10% for low items
            }
            
            // Round to nearest 1000
            $finalCost = round($calculatedCost / 1000) * 1000;
            
            // Ensure minimum cost
            $finalCost = max($finalCost, 5000);
            
            // Update product cost
            $product->cost = $finalCost;
            $product->save();
            
            $updated++;
            
            $this->command->info("Updated: {$product->name} - Price: " . number_format($price, 0) . " - Cost: " . number_format($finalCost, 0) . " ({$categoryName})");
        }
        
        $this->command->info("\n✅ Successfully updated cost for {$updated} products!");
    }
}

