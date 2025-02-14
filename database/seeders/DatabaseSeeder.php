<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            RegionSeeder::class,
            ShopSeeder::class,
            ShopAddressSeeder::class,
            BrandSeeder::class,
            ProductParentCategorySeeder::class,
            ProductSeeder::class,
            ProductSeeder::class,
            ProductAttributeSeeder::class,
            ImageSeeder::class
        ]);
    }
}
