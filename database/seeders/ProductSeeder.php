<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $chunkSize = 10;
        $totalProducts = 50;

        for ($i = 0; $i < $totalProducts; $i += $chunkSize) {
            $chunk = min($chunkSize, $totalProducts - $i);
            
            DB::transaction(function () use ($chunk) {
                Product::factory()->count($chunk)->create()->each(function ($product) {
                    $product->images()->saveMany(Image::factory()->count(5)->make());
                });
            });
        }
    }
}