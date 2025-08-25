<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productImageName = fake()->randomElement([
            'FennecFox.png',
            'blueparrot.png',
            'cookedowl.png',
            'dinozaur.png',
            'discobee.png',
            'dragonfly.png',
            'jakisfryt.png',
            'kitsune.png',
            'kitsuneniebieski.png',
            'mooncat.png',
            'octopus.png',
            'parrot.png',
            'queenbee.png',
            'raccoongag.png',
            'redfox.png',
            'trex.png',
        ]);

        return [
            'game_id' => Game::factory(),
            'user_id' => User::factory()->admin(),

            'name' => ucwords(fake()->unique()->words(3, true)),
            'description' => fake()->realText(),
            'image' => Str::of($productImageName)->prepend('products/'),

            'attributes' => [
                'color' => $this->generateVibrantColor(),
                'rarity' => $this->generateRarity(),
                'discord_emoji' => '<:share:1255874626060423178>',
            ],
            'type' => fake()->randomElement([
                ProductType::CustomItem,
                // ProductType::Account,
                // ProductType::Currency,
            ]),
            'price' => fake()->numberBetween(1, 100) * 100,
            'currency' => CurrencyAlpha3::Euro,
            'min_quantity' => fake()->numberBetween(1, 10),
            'max_quantity' => fake()->numberBetween(10, 20),
        ];
    }

    public function generateVibrantColor(): string
    {
        $hue = random_int(0, 360);
        $saturation = 85;
        $lightness = 65;

        $color =  "hsl(".random_int(0, 360).", 85%,65%)";
    }

    public function generateRarity(): string
    {
        $array = [
            'divine',
            'mythical',
            'legendary',
            'rare',
            'uncommon',
            'common',
        ];

        return $array[array_rand($array)];
    }
}
