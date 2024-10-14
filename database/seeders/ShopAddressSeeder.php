<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Address;
use Illuminate\Database\Seeder;

class ShopAddressSeeder extends Seeder
{
    public function run()
    {
        // Create 10 shops, each with an address
        Shop::factory()
            ->count(10)
            ->has(Address::factory())
            ->create();

        // Output the created shops and their addresses
        $shops = Shop::with('address')->get();
        $this->command->info('Created Shops with Addresses:');
        foreach ($shops as $shop) {
            $this->command->line("Shop ID: {$shop->id}, Name: {$shop->name}");
            $this->command->line("Address: {$shop->address->address_name}, Postal Code: {$shop->address->postal_code}");
            $this->command->line('---');
        }
    }
}