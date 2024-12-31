<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImageSeeder extends Seeder
{
    public function run()
    {
        $imagePaths = [
            'product/guller-22-07-2024-18-30-35/Aoo2O4SXvs.jpg',
            'product/guller-22-07-2024-18-30-35/dL9xWTZuVX.jpg',
            'product/guller-22-07-2024-18-30-35/TWL9yk9ggb.jpg',
            'product/guller-22-07-2024-18-30-35/VYHv7m8Iwg.jpg',
        ];

        $productIds = DB::table('products')->pluck('id');

        foreach ($productIds as $productId) {
            // Shuffle the image paths for random order
            $shuffledPaths = $imagePaths;
            shuffle($shuffledPaths);

            // Insert images in random order for the current product
            foreach ($shuffledPaths as $path) {
                DB::table('images')->insert([
                    'url' => $path,
                    'product_id' => $productId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
