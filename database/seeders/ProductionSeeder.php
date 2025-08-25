<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Game;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminPassword = Str::password();
        $demoPassword = Str::password();
        $adminRandom = Str::random(6);
        $demoRandom = Str::random(6);

        $admin = User::forceCreate([
            'name' => 'Admin User',
            'email' => "admin_{$adminRandom}@zergly.com",
            'email_verified_at' => now(),
            'password' => Hash::make($adminPassword),

            'role' => 'admin',
            'discord_id' => null,

            'remember_token' => Str::random(100),
        ]);

        $demo = User::forceCreate([
            'name' => 'Demo User',
            'email' => "demo_{$demoRandom}@zergly.com",
            'email_verified_at' => now(),
            'password' => Hash::make($demoPassword),

            'role' => 'admin',
            'discord_id' => null,

            'remember_token' => Str::random(100),
        ]);

        $game = Game::create([
            'name' => 'Grow a Garden',
        ]);

        $arrayImg = [
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
        ];

        $array = [
            'divine',
            'mythical',
            'legendary',
            'rare',
            'uncommon',
            'common',
        ];

        foreach ([ProductType::CustomItem, ProductType::Account, ProductType::Currency] as $type) {
            for ($i = 0; $i <= 2; $i++) {
                Product::create([
                    'game_id' => $game->id,
                    'user_id' => $admin->id,

                    'name' => $game->name . ' ' . $type->name . ' ' . $i + 1,
                    'description' => $game->name . ' ' . $type->name . ' ' . $i + 1,
                    'image' => Str::of($arrayImg[array_rand($arrayImg)])->prepend('products/'),
                    'attributes' => [
                        'color' => "hsl(".random_int(0, 360).", 85%,65%)",
                        'rarity' => $array[array_rand($array)],
                        'discord_emoji' => '<:share:1255874626060423178>',
                    ],
                    'type' => $type,
                    'price' => random_int(1, 100) * 100,
                    'currency' => CurrencyAlpha3::Euro,
                    'min_quantity' => random_int(1, 10),
                    'max_quantity' => random_int(10, 20),
                ]);
            }
        }

        $this->command->newLine(1);
        $this->command->table(
            ['Name', 'Email', 'Password'],
            [
                [
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'password' => $adminPassword,
                ],
                [
                    'name' => $demo->name,
                    'email' => $demo->email,
                    'password' => $demoPassword,
                ],
            ]
        );
        $this->command->newLine(1);
    }
}
