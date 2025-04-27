<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Rating;
use App\Models\User;

class RatingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Get all products and users
        $products = Product::all();
        $users = User::all();

        // Check if we have products and users
        if ($products->isEmpty() || $users->isEmpty()) {
            $this->command->info('No products or users found! Please seed products and users first.');
            return;
        }

        // Comments templates
        $comments = [
            'Great product, very satisfied with my purchase!',
            'Good quality but delivery was slow',
            'Exactly as described, would buy again',
            'Not what I expected, but okay',
            'Excellent product, highly recommend',
            'Average quality for the price',
            'Fast shipping and good packaging',
            'Had some issues but seller resolved them',
            'Perfect for my needs',
            'Could be better for the price'
        ];

        foreach ($products as $product) {
            // Create 5-6 ratings per product
            $ratingCount = rand(5, 6);

            // Get random users (ensure unique users per product)
            $randomUsers = $users->random(min($ratingCount, $users->count()))->unique();

            foreach ($randomUsers as $user) {
                Rating::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'num' => rand(3, 5), // Random rating between 3-5 stars
                    'comment' => $comments[array_rand($comments)],
                ]);
            }

            // If we didn't get enough unique users, create additional ratings with repeated users
            if ($randomUsers->count() < $ratingCount) {
                $additionalRatings = $ratingCount - $randomUsers->count();
                for ($i = 0; $i < $additionalRatings; $i++) {
                    Rating::create([
                        'user_id' => $users->random()->id,
                        'product_id' => $product->id,
                        'num' => rand(3, 5),
                        'comment' => $comments[array_rand($comments)],
                    ]);
                }
            }
        }

        $this->command->info('Successfully seeded ratings for products!');
    }
}
