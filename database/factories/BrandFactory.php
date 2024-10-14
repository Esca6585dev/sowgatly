<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition()
    {
        return [
            'name' => Brand::factory(),
            'description' => $this->faker->paragraph(),
            'logo' => $this->generateImageUrl(),
            'status' => $this->faker->boolean,
        ];
    }

    protected function generateImageUrl()
    {
        // List of placeholder image services
        $imageServices = [
            'https://1000logos.net/wp-content/uploads/2021/11/Nike-Logo.png',
            'https://1000logos.net/wp-content/uploads/2019/06/Adidas-Logo.png',
            'https://1000logos.net/wp-content/uploads/2017/05/PUMA-logo.png',
            'https://1000logos.net/wp-content/uploads/2017/06/Under-Armour-Logo.png',
            'https://1000logos.net/wp-content/uploads/2017/05/Reebok-logo.png',
        ];

        return $imageServices[0];
    }
}