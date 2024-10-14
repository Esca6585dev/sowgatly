<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'email' => $this->faker->unique()->safeEmail,
            'mon_fri_open' => $this->faker->time('H:i'),
            'mon_fri_close' => $this->faker->time('H:i'),
            'sat_sun_open' => $this->faker->time('H:i'),
            'sat_sun_close' => $this->faker->time('H:i'),
            'image' => $this->faker->imageUrl(),
            'user_id' => User::factory(),
            'region_id' => Region::factory(),
        ];
    }
}