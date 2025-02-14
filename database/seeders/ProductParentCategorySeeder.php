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
                'name_tm' => $faker->words(3, true) . '_tm',
                'name_en' => $faker->words(3, true) . '_en',
                'name_ru' => $faker->words(3, true) . '_ru',
                
                'price' => $faker->randomFloat(2, 10, 1000),
                'discount' => $faker->optional(0.3)->numberBetween(5, 50),
                
                'description_tm' => $faker->paragraph . '_tm',
                'description_en' => $faker->paragraph . '_en',
                'description_ru' => $faker->paragraph . '_ru',

                'production_time' => $faker->numberBetween(60, 1440),  // in minutes (1 hour to 24 hours)
                'min_order' => $faker->optional()->numberBetween(1, 10),
                
                'seller_status' => $faker->boolean,
                'status' => $faker->boolean,
                
                'stock' => $faker->numberBetween(1, 100),

                'shop_id' => $faker->randomElement($shopIds),
                'category_id' => $faker->randomElement($categoryIds),

                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 month', 'now'),
            ]);
        }
    }
}