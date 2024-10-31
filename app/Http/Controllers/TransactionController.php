<?php

namespace App\Http\Controllers;

use App\Helpers\DigiflazzHelper;
use App\Helpers\MyHelper;
use App\Models\Digiflazz;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Telegram\Bot\Api;

class TransactionController extends Controller
{
    protected $telegram;

    public function __construct(Api $telegram)
    {
        return $this->telegram = $telegram;
    }

    public function create(Request $request)
    {
        $userTelId = $request->user_tel_id;

        $user = User::where('user_tel_id', $userTelId)->first();
        $product = Digiflazz::where('buyer_sku_code', $request->buyerSkuCode)->first();
        $mode = DigiflazzHelper::getMode();
        $username = DigiflazzHelper::getUsername();
        $invoice = MyHelper::invoice($user->id, 'TRX', 'transactions');
        $sign = DigiflazzHelper::getSign($invoice);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Produk ' . $request->buyerSkuCode . ' tidak ditemukan'
            ]);
        }

        if ($user->saldo < $product->price) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Sisa Saldo: Rp.' . $user->saldo
            ]);
        }

        if ($mode === 'dev') {
            $data = [
                'username' => $username,
                'buyer_sku_code' => 'xld10',
                'customer_no' => '087800001233',
                'ref_id' => $invoice,
                'sign' => $sign,
                'testing' => true,
            ];
        } else if ($mode === 'prod') {
            $data = [
                'username' => $username,
                'buyer_sku_code' => $request->buyerSkuCode,
                'customer_no' => $request->target,
                'ref_id' => $invoice,
                'sign' => $sign
            ];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan transaksi.',
            ], 400);
        }

        $result = DigiflazzHelper::transaction('transaction', $data);

        if (isset($result->data)) {
            if (isset($result->data->rc) && $result->data->rc !== "00" || $result->data->rc !== "03") {
                $price = number_format($product->price, 0, '.', '.');

                $this->telegram->sendMessage([
                    'chat_id' => $user->chat_id,
                    'user_tel_id' => $user->user_tel_id,
                    'text' => "Saldo dikembalikan Rp.$price",
                ]);
            }
        }

        $result = Transaction::create([
            'user_id' => $user->id,
            'invoice' => $invoice,
            'target' => $request->target,
            'buyer_sku_code' => $request->buyerSkuCode,
            'product_name' => $product->product_name,
            'price' => number_format($product->price, 0, '.', '.'),
            'message' => $result->data->message,
            'sn' => $result->data->sn,
            'status' => $result->data->status,
        ]);

        $user->update([
            'saldo' => $user->saldo - $product->price
        ]);

        $result['saldo'] = number_format($user->saldo, 0, '.', '.');

        return response()->json([
            'success' => true,
            'message' => 'Berhasil melakukan pemesanan.',
            'data' => $result
        ]);
    }

    public function status(Request $request)
    {
        $userTelId = $request->user_tel_id;
        $target = $request->target;

        $user = User::where('user_tel_id', $userTelId)->first();
        $transaction = Transaction::where('user_id', $user->id)->where('target', $target)->orderBy('created_at', 'DESC')->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail transaction',
            'data' => $transaction
        ]);
    }

    public function histories(Request $request)
    {
        $userTelId = $request->user_tel_id;

        $user = User::where('user_tel_id', $userTelId)->first();
        $transaction = Transaction::where('user_id', $user->id)->latest()->limit(10)->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat transaksi',
            'data' => $transaction
        ]);
    }
}
