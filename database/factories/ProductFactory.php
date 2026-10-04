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
            'description_tm' => $this->faker->sentence(),
            'description_en' => $this->faker->sentence(),
            'description_ru' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'discount' => 0,
            'production_time' => $this->faker->numberBetween(60, 1440),
            'min_order' => 1,
            'stock' => 10,
            'seller_status' => true,
            'status' => true,
            'shop_id' => Shop::factory(),
            'category_id' => Category::factory(),
        ];
    }

    /** Every product gets three images, like real catalog entries. */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            Image::factory()->count(3)->forProduct($product)->create();
        });
    }

    /** Product that is hidden from the storefront. */
    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }
}
