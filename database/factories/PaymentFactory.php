<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(5000, 10000),
            'currency' => CurrencyAlpha3::Zloty,
            'method' => fake()->randomElement([
                PaymentMethod::Transfer,
                PaymentMethod::PaySafeCard,
            ]),
            'external_id' => fake()->bothify('payment-?????-#####'),
        ];
    }
}
