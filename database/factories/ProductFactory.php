<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name_tm' => $name,
            'name_en' => $name,
            'name_ru' => $name,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'discount' => $this->faker->optional()->numberBetween(5, 50),
            'description_tm' => $this->faker->paragraph(),
            'description_en' => $this->faker->paragraph(),
            'description_ru' => $this->faker->paragraph(),
            'production_time' => $this->faker->numberBetween(60, 1440),
            'min_order' => 1,
            'stock' => $this->faker->numberBetween(1, 50),
            'seller_status' => true,
            'status' => true,
            'shop_id' => Shop::factory(),
            'category_id' => Category::factory(),
        ];
    }

    /** Product that is hidden from the storefront. */
    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }

    /** Product with its images already attached. */
    public function withImages(int $count = 2): static
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            Image::factory()->count($count)->forProduct($product)->create();
        });
    }
}
