<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
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
            'amount' => $this->faker->numberBetween(1000, 9000),
            'currency' => CurrencyAlpha3::Euro,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            Payment::factory()
                ->for($order, 'paymentable')
                ->create([
                    'user_id' => $order->user_id,
                    'amount' => $order->amount,
                ]);

            $products = Product::inRandomOrder()->take(rand(2, 4))->get();
            foreach ($products as $product) {
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 2),
                ]);
            }

            $admin = User::where('role', 'admin')->inRandomOrder()->first();

            $t0 = now();
            $messages = [
                ['user_id' => null, 'message' => 'Twoje zamówienie zostało przyjęte! Admin już się nim zajmuje.'],
                ['user_id' => $admin?->id, 'message' => 'Cześć! W czym mogę pomóc?'],
                ['user_id' => $order->user_id, 'message' => 'Hej, chciałbym status realizacji.'],
                ['user_id' => $admin?->id, 'message' => 'W trakcie — dam znać za chwilę.'],
            ];

            foreach ($messages as $i => $data) {
                OrderMessage::factory()->create([
                    'order_id' => $order->id,
                    'user_id' => $data['user_id'],
                    'message' => $data['message'],
                    'created_at' => $t0->copy()->addSeconds($i),
                    'updated_at' => $t0->copy()->addSeconds($i),
                ]);
            }
        });
    }
}
