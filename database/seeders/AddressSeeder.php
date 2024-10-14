<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run()
    {
        // First, create shops without addresses
        Shop::factory()->count(10)->create();

        // Then, create an address for each shop
        Shop::all()->each(function ($shop) {
            Address::factory()->create(['shop_id' => $shop->id]);
        });
    }
}