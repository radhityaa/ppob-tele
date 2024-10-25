<?php

namespace Database\Seeders;

use App\Models\SettingProvider;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            [
                'name' => 'tripay',
                'slug' => Str::slug(Str::random()),
                'mode' => 'dev',
                'type' => 'payment_gateway',
                'api_key' => 'DEV-1TnkVMJheFh0QQl5IpGzo9EZ3RSnYPymCIm614FJ',
                'private_key' => 'pT37T-VbaCy-tPZqp-JhojK-LDLnS',
                'code' => 'T29295',
            ],
            [
                'name' => 'digiflazz',
                'slug' => Str::slug(Str::random()),
                'mode' => 'dev',
                'type' => 'product',
                'api_key' => 'dev-3446da70-e3a8-11eb-9cf1-bbae9ce189b4',
                'username' => 'rukasoDb7pkW',
                'webhook_id' => 'gdlEPg',
                'webhook_url' => 'https://ppob.ayasyatech.com/digiflazz/callback',
                'webhook_secret' => 'awdklnkl12k3nuibguy1ghkansiuy8iu1h2b3kjbkjawbdywg1iu2h3u12kj3bkjbwaiudh8i12hn3jk21n3kjbwads'
            ],
            [
                'name' => 'wagw',
                'slug' => Str::slug(Str::random()),
                'mode' => 'prod',
                'type' => 'whatsapp_gateway',
                'api_key' => 'mNov8FbOSLUbmLn'
            ],
        ])->each(fn($data) => SettingProvider::create($data));
    }
}
