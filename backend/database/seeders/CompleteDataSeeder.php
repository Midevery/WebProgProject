<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteDataSeeder extends Seeder
{
    public function run(): void
    {
        $laptops = Category::create(['name' => 'Laptops', 'slug' => 'laptops', 'description' => 'Used laptops and notebooks']);
        $desktops = Category::create(['name' => 'Desktops', 'slug' => 'desktops', 'description' => 'Used desktop computers']);
        $smartphones = Category::create(['name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'Used smartphones']);
        $tablets = Category::create(['name' => 'Tablets', 'slug' => 'tablets', 'description' => 'Used tablets']);
        $accessories = Category::create(['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Tech accessories and peripherals']);

        $sellers = [];
        $sellerNames = [
            'TechSeller Pro', 'LaptopHub', 'PhoneDealer', 'GadgetStore', 'TechOutlet',
            'SecondHand Tech', 'Refurbished Hub', 'TechBargain', 'DeviceMarket', 'TechReseller'
        ];
        
        for ($i = 0; $i < 10; $i++) {
            $sellers[] = User::create([
                'username' => 'seller' . ($i + 1),
                'name' => $sellerNames[$i],
                'email' => 'seller' . ($i + 1) . '@refurbworks.com',
                'password' => Hash::make('seller123'),
                'role' => 'seller',
                'phone' => '08' . str_pad(rand(100000000, 999999999), 10, '0', STR_PAD_LEFT),
                'address' => fake()->address(),
                'balance' => fake()->numberBetween(500000, 5000000),
                'gender' => fake()->randomElement(['Male', 'Female']),
                'date_of_birth' => fake()->date('Y-m-d', '-30 years'),
            ]);
        }

        $customers = [];
        for ($i = 0; $i < 20; $i++) {
            $customers[] = User::create([
                'username' => 'customer' . ($i + 1),
                'name' => fake()->name(),
                'email' => 'customer' . ($i + 1) . '@gmail.com',
                'password' => Hash::make('customer123'),
                'role' => 'customer',
                'phone' => '08' . str_pad(rand(100000000, 999999999), 10, '0', STR_PAD_LEFT),
                'address' => fake()->address(),
                'gender' => fake()->randomElement(['Male', 'Female']),
                'date_of_birth' => fake()->date('Y-m-d', '-25 years'),
            ]);
        }

        $products = [];
        $laptopProducts = [
            ['name' => 'Lenovo ThinkPad X1 Carbon Gen 8 - Intel Core i5-10210U', 'price' => 8500000, 'cost' => 6000000],
            ['name' => 'Dell Latitude 5420 - Intel Core i5-1135G7', 'price' => 7200000, 'cost' => 5000000],
            ['name' => 'HP EliteBook 840 G7 - Intel Core i5-10210U', 'price' => 7800000, 'cost' => 5500000],
            ['name' => 'Lenovo ThinkPad T480 - Intel Core i5-8250U', 'price' => 6500000, 'cost' => 4500000],
            ['name' => 'Dell XPS 13 9300 - Intel Core i5-1035G1', 'price' => 9500000, 'cost' => 7000000],
            ['name' => 'MacBook Air 2017 - Intel Core i5', 'price' => 8500000, 'cost' => 6000000],
            ['name' => 'ASUS VivoBook S14 - Intel Core i5-8265U', 'price' => 6800000, 'cost' => 4800000],
            ['name' => 'Acer Aspire 5 - Intel Core i5-10210U', 'price' => 6200000, 'cost' => 4200000],
            ['name' => 'Lenovo ThinkPad E14 - Intel Core i5-10210U', 'price' => 7000000, 'cost' => 4900000],
            ['name' => 'HP ProBook 450 G7 - Intel Core i5-10210U', 'price' => 7500000, 'cost' => 5300000],
        ];

        $smartphoneProducts = [
            ['name' => 'iPhone 12 - 64GB - Black', 'price' => 6500000, 'cost' => 4500000],
            ['name' => 'Samsung Galaxy S21 - 128GB - Phantom Gray', 'price' => 7200000, 'cost' => 5000000],
            ['name' => 'iPhone 11 - 128GB - Purple', 'price' => 5800000, 'cost' => 4000000],
            ['name' => 'Xiaomi Mi 11 - 128GB - Blue', 'price' => 5500000, 'cost' => 3800000],
            ['name' => 'OnePlus 9 - 128GB - Winter Mist', 'price' => 6800000, 'cost' => 4700000],
            ['name' => 'Samsung Galaxy Note 20 - 256GB', 'price' => 8000000, 'cost' => 5500000],
            ['name' => 'iPhone XR - 128GB - Red', 'price' => 5000000, 'cost' => 3500000],
            ['name' => 'Google Pixel 5 - 128GB', 'price' => 6000000, 'cost' => 4200000],
        ];

        $tabletProducts = [
            ['name' => 'iPad Air 4th Gen - 64GB - Space Gray', 'price' => 7500000, 'cost' => 5300000],
            ['name' => 'Samsung Galaxy Tab S7 - 128GB - Mystic Black', 'price' => 6800000, 'cost' => 4800000],
            ['name' => 'iPad Pro 11" 2nd Gen - 256GB - Silver', 'price' => 12000000, 'cost' => 8500000],
        ];

        $accessoryProducts = [
            ['name' => 'Logitech MX Master 3 Wireless Mouse', 'price' => 850000, 'cost' => 500000],
            ['name' => 'Mechanical Keyboard - RGB Backlit', 'price' => 650000, 'cost' => 400000],
            ['name' => 'USB-C Hub - 7-in-1', 'price' => 350000, 'cost' => 200000],
            ['name' => 'External SSD 500GB', 'price' => 750000, 'cost' => 500000],
            ['name' => 'Wireless Headphones - Noise Cancelling', 'price' => 1200000, 'cost' => 800000],
            ['name' => 'Laptop Stand - Aluminum', 'price' => 450000, 'cost' => 250000],
        ];

        $allProductData = array_merge(
            array_map(fn($p) => array_merge($p, ['category' => $laptops]), $laptopProducts),
            array_map(fn($p) => array_merge($p, ['category' => $smartphones]), $smartphoneProducts),
            array_map(fn($p) => array_merge($p, ['category' => $tablets]), $tabletProducts),
            array_map(fn($p) => array_merge($p, ['category' => $accessories]), $accessoryProducts)
        );

        foreach ($allProductData as $index => $productData) {
            $products[] = Product::create([
                'name' => $productData['name'],
                'description' => 'Used ' . $productData['name'] . '. Condition: ' . fake()->randomElement(['Excellent', 'Very Good', 'Good', 'Fair']) . '. ' . fake()->sentence(),
                'price' => $productData['price'],
                'cost' => $productData['cost'],
                'stock' => fake()->numberBetween(1, 10),
                'image' => 'https://picsum.photos/600/800?random=' . ($index + 300),
                'category_id' => $productData['category']->id,
                'seller_id' => fake()->randomElement($sellers)->id,
                'clicks' => fake()->numberBetween(0, 1000),
                'sales_count' => fake()->numberBetween(0, 50),
            ]);
        }

        for ($i = 0; $i < 30; $i++) {
            $customer = fake()->randomElement($customers);
            $orderProducts = fake()->randomElements($products, fake()->numberBetween(1, 3));
            
            $subtotal = collect($orderProducts)->sum(function($product) {
                return $product->price * fake()->numberBetween(1, 2);
            });
            
            $platformFee = 2000;
            $tax = $subtotal * 0.1;
            $shippingPrice = fake()->randomElement([10000, 15000, 20000]);
            $total = $subtotal + $platformFee + $tax + $shippingPrice;

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $customer->id,
                'total_amount' => $total,
                'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
                'shipping_address' => $customer->address ?? fake()->address(),
                'shipping_method' => fake()->randomElement(['fast', 'regular', 'economy']),
                'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
            ]);

            foreach ($orderProducts as $product) {
                $quantity = fake()->numberBetween(1, 2);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'subtotal' => $product->price * $quantity,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => fake()->randomElement(['va', 'e_wallet', 'credit_card']),
                'status' => $order->status === 'pending' ? 'pending' : 'paid',
                'amount' => $total,
                'paid_at' => $order->status !== 'pending' ? $order->created_at->addHours(2) : null,
                'transaction_id' => $order->status !== 'pending' ? 'TXN-' . strtoupper(uniqid()) : null,
            ]);

            if ($order->status !== 'pending') {
                $firstProduct = $orderProducts[0];
                $sellerId = $firstProduct->seller_id;
                
                Shipping::create([
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'status' => $order->status === 'delivered' ? 'delivered' : ($order->status === 'shipped' ? 'shipped' : 'processing'),
                    'tracking_number' => $order->status !== 'pending' ? 'TRK-' . strtoupper(uniqid()) : null,
                    'courier' => 'JNE - ' . ucfirst($order->shipping_method),
                    'shipped_at' => $order->status === 'delivered' || $order->status === 'shipped' ? $order->created_at->addDays(1) : null,
                    'delivered_at' => $order->status === 'delivered' ? $order->created_at->addDays(3) : null,
                ]);
            }
        }

        for ($i = 0; $i < 15; $i++) {
            Cart::create([
                'user_id' => fake()->randomElement($customers)->id,
                'product_id' => fake()->randomElement($products)->id,
                'quantity' => fake()->numberBetween(1, 3),
            ]);
        }

        for ($i = 0; $i < 20; $i++) {
            Wishlist::firstOrCreate([
                'user_id' => fake()->randomElement($customers)->id,
                'product_id' => fake()->randomElement($products)->id,
            ]);
        }

        for ($i = 0; $i < 25; $i++) {
            Comment::create([
                'user_id' => fake()->randomElement($customers)->id,
                'product_id' => fake()->randomElement($products)->id,
                'comment' => fake()->sentence(10),
                'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ]);
        }

    }
}

