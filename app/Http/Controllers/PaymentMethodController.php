<?php

namespace App\Http\Controllers;

use App\Helpers\TripayHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function getPaymentProvider($provider)
    {
        if ($provider === 'tripay') {
            $result = TripayHelper::getChannels();

            if (isset($result['success']) && $result['success'] === true) {
                if (isset($result['data']) && is_array($result['data'])) {
                    foreach ($result['data'] as $channel) {
                        PaymentMethod::updateOrCreate(
                            ['code' => $channel['code']],
                            [
                                'name' => $channel['name'],
                                'slug' => Str::slug($channel['name'] . "-" . Str::random(6)),
                                'group' => $channel['group'],
                                'code' => $channel['code'],
                                'fee' => $channel['total_fee']['flat'],
                                'percent_fee' => $channel['total_fee']['percent'],
                                'icon_url' => $channel['icon_url'],
                                'status' => $channel['active'],
                                'provider' => 'tripay'
                            ]
                        );
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil mengambil data.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => isset($result['message']) ? $result['message'] : 'Terjadi kesalahan saat mengambil data dari Tripay.',
                ], 400); // Menggunakan status 400 (Bad Request) untuk menandakan kesalahan
            }
        }
    }
}
