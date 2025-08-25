<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeder = match ($environment = app()->environment()) {
            'local' => LocalSeeder::class,
            'production' => ProductionSeeder::class,

            default => throw new RuntimeException(
                sprintf(
                    'No seeder configured for the "%s" environment. Please check your DatabaseSeeder or create one for this environment.',
                    $environment
                )
            ),
        };

        $this->call($seeder);
    }
}
