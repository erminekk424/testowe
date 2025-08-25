<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->count(2)->create();
        $games = Game::factory()->count(3)->create();

        foreach (range(1, 1000) as $i) {
            Product::factory()->create([
                'game_id' => $games->random()->id,
                'user_id' => $admin->id,
            ]);
        }

        foreach ($users as $u) {
            Order::factory()
                ->count(rand(2, 4))
                ->forUser($u)
                ->create();
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'discord_id' => '1229422750733438979',
        ]);
    }
}
