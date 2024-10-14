<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition()
    {
        return [
            'url' => $this->generateImageUrl(),
            'product_id' => Product::factory(),
        ];
    }

    public function forProduct(Product $product)
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product->id,
            ];
        });
    }

    protected function generateImageUrl()
    {
        // List of placeholder image services
        $imageServices = [
            'https://picsum.photos/id/{id}/300/300',
            'https://loremflickr.com/300/300/product?lock={id}',
            'https://source.unsplash.com/300x300/?product&sig={id}',
        ];

        // Choose a random service
        $service = $this->faker->randomElement($imageServices);

        // Generate a random ID
        $id = $this->faker->numberBetween(1, 1000);

        // Replace {id} in the URL with the generated ID
        return str_replace('{id}', $id, $service);
    }
}