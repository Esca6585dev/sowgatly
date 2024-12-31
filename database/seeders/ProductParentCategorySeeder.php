<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProductParentCategorySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Assuming you have shops and categories already seeded
        $shopIds = DB::table('shops')->pluck('id')->toArray();
        $categoryIds = DB::table('categories')->where('category_id', null)->pluck('id')->toArray();

        foreach (range(1, 500) as $index) {  // Create 500 products
            DB::table('products')->insert([
                'name' => $faker->words(3, true),
                'price' => $faker->randomFloat(2, 10, 1000),
                'discount' => $faker->optional(0.3)->numberBetween(5, 50),
                'description' => $faker->paragraph,
                'gender' => $faker->randomElement(['Men', 'Women', 'Children']),
                'sizes' => json_encode($faker->randomElements(range(42, 50), $faker->numberBetween(1, 5))),
                'separated_sizes' => json_encode($faker->randomElements(['S', 'M', 'L', 'XL', 'XXL'], $faker->numberBetween(1, 5))),
                'color' => $faker->safeColorName,
                'manufacturer' => $faker->country,
                'width' => $faker->randomFloat(2, 10, 100),
                'height' => $faker->randomFloat(2, 10, 100),
                'weight' => $faker->numberBetween(100, 5000),  // in grams
                'production_time' => $faker->numberBetween(60, 1440),  // in minutes (1 hour to 24 hours)
                'min_order' => $faker->optional()->numberBetween(1, 10),
                'seller_status' => $faker->boolean,
                'status' => $faker->boolean,
                'shop_id' => $faker->randomElement($shopIds),
                'category_id' => $faker->randomElement($categoryIds),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);
        }
    }
}