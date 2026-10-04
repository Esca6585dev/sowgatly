<?php

namespace Database\Factories;

use App\Models\FavoriteCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteCollectionFactory extends Factory
{
    protected $model = FavoriteCollection::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'position' => 0,
        ];
    }
}
