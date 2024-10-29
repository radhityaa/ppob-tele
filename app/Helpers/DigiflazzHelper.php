<?php

namespace App\Helpers;

use App\Models\SettingProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DigiflazzHelper
{
    public static function getData()
    {
        return SettingProvider::where('name', 'digiflazz')->first();
    }

    public static function getMode()
    {
        return self::getData()->mode;
    }

    public static function getUsername()
    {
        return self::getData()->username;
    }

    public static function getKey()
    {
        return self::getData()->api_key;
    }

    public static function getWebhookId()
    {
        return self::getData()->webhook_id;
    }

    public static function getWebhookUrl()
    {
        return self::getData()->webhook_url;
    }

    public static function getWebhookSecret()
    {
        return self::getData()->webhook_secret;
    }

    public static function getSign(string $type)
    {
        return md5(self::getUsername() . self::getKey() . $type);
    }

    public static function getService(string $type)
    {
        $data = [
            'cmd' => $type,
            'username' => self::getUsername(),
            'sign' => self::getSign('pricelist')
        ];

        return self::transaction('price-list', $data);
    }

    public static function transaction(string $url, array $data)
    {
        $response = Http::post('https://api.digiflazz.com/v1/' . $url, $data);
        return json_decode($response);
    }

    public static function validasiTokenPln($target)
    {
        $data = [
            'commands' => 'pln-subscribe',
            'customer_no' => $target
        ];

        return self::transaction('transaction', $data);
    }
}
