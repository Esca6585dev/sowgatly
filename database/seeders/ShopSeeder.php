<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\Region;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $region = $this->getOrCreateRegion();
            $this->createShops($region);
        });
    }

    /**
     * Get or create a region.
     *
     * @return Region
     */
    private function getOrCreateRegion(): Region
    {
        return Region::firstOrCreate(['name' => 'Default Region']);
    }

    /**
     * Create shops.
     *
     * @param Region $region
     */
    private function createShops(Region $region): void
    {
        $shopsData = [
            [
                'name' => 'Moda House',
                'email' => 'esca656585@gmail.com',
                'mon_fri_open' => '09:00',
                'mon_fri_close' => '18:00',
                'sat_sun_open' => '09:00',
                'sat_sun_close' => '13:00',
                'image' => 'shop/shop-seeder/modahouse-logo.jpg',
                'user_id' => $this->getUserId(1),
            ],
            [
                'name' => 'Sowgatly',
                'email' => 'esca6585@gmail.com',
                'mon_fri_open' => '10:00',
                'mon_fri_close' => '19:00',
                'sat_sun_open' => 'işlänok',
                'sat_sun_close' => 'işlänok',
                'image' => 'shop/shop-seeder/sowgatly-logo.png',
                'user_id' => $this->getUserId(2),
            ],
        ];

        foreach ($shopsData as $shopData) {
            $shop = Shop::firstOrCreate(
                [
                    'name' => $shopData['name'],
                    'email' => $shopData['email'],
                ],
                array_merge($shopData, [
                    'region_id' => $region->id,
                ])
            );

            $this->createAddress($shop);
        }
    }

    /**
     * Get or create a user ID.
     *
     * @param int $userId
     * @return int
     */
    private function getUserId(int $userId): int
    {
        $user = User::firstOrCreate(
            ['id' => $userId],
            [
                'name' => 'User ' . $userId,
                'email' => 'user' . $userId . '@example.com',
                'password' => bcrypt('password'),
            ]
        );

        return $user->id;
    }

    /**
     * Create an address for a shop.
     *
     * @param Shop $shop
     */
    private function createAddress(Shop $shop): void
    {
        Address::firstOrCreate(
            ['shop_id' => $shop->id],
            [
                'address_name' => fake()->streetAddress,
                'postal_code' => fake()->postcode,
            ]
        );
    }
}