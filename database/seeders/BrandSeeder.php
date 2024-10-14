<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $brands = [
            [
                'name' => 'Nike',
                'description' => 'Just Do It',
                'logo' => 'https://1000logos.net/wp-content/uploads/2021/11/Nike-Logo.png',
                'status' => true,
            ],
            [
                'name' => 'Adidas',
                'description' => 'Impossible Is Nothing',
                'logo' => 'https://1000logos.net/wp-content/uploads/2019/06/Adidas-Logo.png',
                'status' => true,
            ],
            [
                'name' => 'Puma',
                'description' => 'Forever Faster',
                'logo' => 'https://1000logos.net/wp-content/uploads/2017/05/PUMA-logo.png',
                'status' => true,
            ],
            [
                'name' => 'Reebok',
                'description' => 'Be More Human',
                'logo' => 'https://1000logos.net/wp-content/uploads/2017/05/Reebok-logo.png',
                'status' => true,
            ],
            [
                'name' => 'Under Armour',
                'description' => 'I Will',
                'logo' => 'https://1000logos.net/wp-content/uploads/2017/06/Under-Armour-Logo.png',
                'status' => true,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}