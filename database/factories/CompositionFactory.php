<?php

namespace Database\Factories;

use App\Models\Composition;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompositionFactory extends Factory
{
    protected $model = Composition::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
        ];
    }
}