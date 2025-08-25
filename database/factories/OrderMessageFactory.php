<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderMessageFactory extends Factory
{
    protected $model = OrderMessage::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => null,
            'message' => $this->faker->sentence(),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function from(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
