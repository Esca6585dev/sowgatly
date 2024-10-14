<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->city,
            'type' => $this->faker->randomElement(['country', 'province', 'city', 'village']),
            'parent_id' => null, // You might want to adjust this based on your needs
        ];
    }

    /**
     * Indicate that the region is a country.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function country()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'country',
                'parent_id' => null,
            ];
        });
    }

    /**
     * Indicate that the region is a province.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function province()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'province',
                'parent_id' => Region::factory()->country(),
            ];
        });
    }

    /**
     * Indicate that the region is a city.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function city()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'city',
                'parent_id' => Region::factory()->province(),
            ];
        });
    }

    /**
     * Indicate that the region is a village.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function village()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'village',
                'parent_id' => Region::factory()->city(),
            ];
        });
    }
}