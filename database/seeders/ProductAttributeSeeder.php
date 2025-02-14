<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample product data
        $attributes = [
            [
                'key' => 'color', 
                'values' => [
                    'black', 'red', 'brown', "#ccc", "#fff", "#333", "#f0f"
                ]
            ],
            [
                'key' => 'a-size', 
                'values' => [
                    'XS', 'S', 'M', 'L', 'XL'
                ]
            ],
            [
                'key' => 'n-size',
                'values' => [
                    '38', '39', '40', '41', '42'
                ]
            ],
            [
                'key' => 'height', 
                'values' => [
                    '10', '20', '30', '40', '50'
                ]
            ],
            [
                'key' => 'width', 
                'values' => [
                    'XS', 'S', 'M', 'L', 'XL'
                ]
            ],
            [
                'key' => 'storage', 
                'values' => [
                    '16 GB', '64 GB', '128 GB', '512 GB', '1 TB', '2 TB'
                ]
            ],
        ];

        $products = Product::all();

        foreach ($products as $product) {
            foreach ($attributes as $attribute) {
                $product->attributes()->create([
                    'attribute_key' => $attribute['key'],
                    'attribute_value' => json_encode($attribute['values']),
                ]);
            }
        }
    }
}