<?php

namespace App\Helpers;

use App\Models\SettingProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayHelper
{
    public static function getData()
    {
        return SettingProvider::where('name', 'tripay')->first();
    }

    public static function getMode()
    {
        $mode = self::getData()->mode;

        if ($mode == 'dev') {
            return 'https://tripay.co.id/api-sandbox';
        } else if ($mode == 'prod') {
            return 'https://tripay.co.id/api';
        }
    }

    public static function getPrivateKey()
    {
        return self::getData()->private_key;
    }

    public static function getApiKey()
    {
        return self::getData()->api_key;
    }

    public static function getMerchantCode()
    {
        return self::getData()->code;
    }

    public static function generateSignature($merchantRef, $amount)
    {
        $privateKey = self::getPrivateKey();
        $merchantCode = self::getMerchantCode();

        return hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);
    }

    public static function getChannels()
    {
        $mode = self::getMode();
        $response = Http::withToken(self::getApiKey())->get($mode . '/merchant/payment-channel');
        return json_decode($response->body(), true);
    }

    public static function createDepositLocal($nominal, $method, array $orderItems, $chatId)
    {
        try {
            $mode = self::getMode();
            $user = User::where('chat_id', $chatId)->first();
            $invoice = MyHelper::invoice($user->id, 'DPS');
            $signature = self::generateSignature($invoice, $nominal);

            $data = [
                'method' => $method,
                'merchant_ref' => $invoice,
                'amount' => $nominal,
                'customer_name' => $user->name,
                'customer_email' => 'customer@gmail.com',
                'customer_phone' => $user->phone,
                'order_items' => $orderItems,
                'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
                'signature'    => $signature
            ];

            $response = Http::withToken(self::getApiKey())->post($mode . '/transaction/create', $data);

            return json_decode($response->body());
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ];
        }
    }

    public static function createDepositWa($nominal, $method, array $orderItems, $user)
    {
        try {
            $mode = self::getMode();
            $invoice = MyHelper::invoice($user->id, 'DPS');
            $signature = self::generateSignature($invoice, $nominal);

            $data = [
                'method' => $method,
                'merchant_ref' => $invoice,
                'amount' => $nominal,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
                'order_items' => $orderItems,
                'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
                'signature'    => $signature
            ];

            $response = Http::withToken(self::getApiKey())->post($mode . '/transaction/create', $data);
            return json_decode($response->body());
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ];
        }
    }
}
