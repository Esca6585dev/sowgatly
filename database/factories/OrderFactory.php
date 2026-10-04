<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shop_id' => Shop::factory(),
            'total_amount' => $this->faker->randomFloat(2, 50, 2000),
            'status' => 'pending',
            'delivery_type' => 'asap',
            'scheduled_at' => null,
            'recipient_phone' => $this->faker->numerify('6#######'),
            'delivery_address' => $this->faker->streetAddress(),
            'note' => null,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
