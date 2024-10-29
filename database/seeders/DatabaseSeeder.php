<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Margin;
use App\Models\TokenBot;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::create([
        //     'name' => 'Rama Adhitya Setiadi',
        //     'user_id' => 1872192068,
        //     'phone' => '0895347113987',
        //     'shop_name' => 'AyasyaTech',
        //     'status' => 'active',
        //     'saldo' => 0,
        // ]);

        TokenBot::create([
            'token' => '7771619357:AAF5QfYzUIgGZMFwlClbGszAl2Q5JgZevcg'
        ]);

        Margin::create([
            'margin' => 0
        ]);

        $this->call([
            SettingProviderSeeder::class
        ]);
    }
}
