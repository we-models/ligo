<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //php artisan passport:client --personal
        $this->call([
            ConfigTypeSeeder::class,
            IconSeeder::class,
            FirstInstallationSeeder::class,
            ConfigurationsSeeder::class//,
            //WeModelSeeder::class
        ]);
    }
}
