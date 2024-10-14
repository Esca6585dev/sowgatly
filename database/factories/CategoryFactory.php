<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'name_tm' => $this->faker->word,
            'name_en' => $this->faker->word,
            'name_ru' => $this->faker->word,
            'image' => $this->faker->imageUrl(),
            'category_id' => null,
        ];
    }
}