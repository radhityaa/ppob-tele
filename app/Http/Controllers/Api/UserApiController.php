<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TripayHelper;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Ramsey\Uuid\Uuid;

class UserApiController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'chat_id' => ['required', 'unique:users'],
            'user_tel_id' => ['required', 'unique:users'],
            'name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'shop_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::create([
            'chat_id' => $request->chat_id,
            'user_tel_id' => $request->user_tel_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'shop_name' => $request->shop_name,
            'status' => 'active'
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Successfully created',
            'data' => $user
        ]);
    }

    public function check(Request $request)
    {
        $userTelId = $request->user_tel_id;
        $user = User::where('user_tel_id', $userTelId)->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'User Resgistered'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User Not Found'
        ]);
    }

    public function checkToken(Request $request)
    {
        $userTelId = $request->user_tel_id;
        $user = User::where('user_tel_id', $userTelId)->first();

        if ($user) {
            if (!$user->user_token) {
                $user->update([
                    'user_token' => Uuid::uuid4()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $user->user_token
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User Not Found'
        ]);
    }

    public function saldo(Request $request)
    {
        $userTelId = $request->user_tel_id;
        $user = User::where('user_tel_id', $userTelId)->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Saldo Available',
                'data' => 'Rp.' . number_format($user->saldo, 0, '.', '.')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User Not Found'
        ]);
    }

    public function deposit(Request $request)
    {
        $userTelId = $request->user_tel_id;
        $nominal = $request->nominal;
        $user = User::where('user_tel_id', $userTelId)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User Not Found'
            ]);
        }

        $paymentMethod = PaymentMethod::where('code', 'QRIS2')->first();

        $flatFee = $paymentMethod->fee;
        $percentFee = $paymentMethod->percent_fee;
        $fee = $flatFee;

        if ($percentFee > 0) {
            $fee = ($nominal * ($percentFee / 100) + $flatFee);
        }

        $orderItems = [
            [
                'name'        => 'Deposit Saldo',
                'price'       => $nominal,
                'quantity'    => 1,
            ]
        ];

        $response = TripayHelper::createDepositLocal($nominal, 'QRIS2', $orderItems, $userTelId);

        if (!$response->success) {
            return response()->json([
                'success' => false,
                'message' => $response->message
            ]);
        }

        $expired_time = Carbon::createFromTimestamp($response->data->expired_time)->toDateTimeString();

        $result = Deposit::create([
            'user_id' => $user->id,
            'invoice' => $response->data->merchant_ref,
            'method' => $response->data->payment_name,
            'nominal' => $nominal,
            'fee' => $fee,
            'total' => $response->data->amount,
            'amount_received' => $response->data->amount_received,
            'pay_code' => $response->data->pay_code,
            'pay_url' => $response->data->pay_url,
            'checkout_url' => $response->data->checkout_url,
            'status' => $response->data->status,
            'expired_at' => $expired_time
        ]);

        $data = [
            'user_name' => $user->name,
            'invoice' => $response->data->merchant_ref,
            'method' => $response->data->payment_name,
            'nominal' => 'Rp.' . number_format($nominal, 0, '.', '.'),
            'fee' => 'Rp.' . number_format($fee, 0, '.', '.'),
            'total' => 'Rp.' . number_format($response->data->amount, 0, '.', '.'),
            'amount_received' => 'Rp.' . number_format($response->data->amount_received, 0, '.', '.'),
            'pay_url' => $response->data->checkout_url,
            'status' => $response->data->status,
            'expired_at' => $result->expired_at->format('d-m-Y H:i:s')
        ];

        return response()->json([
            'success' => true,
            'message' => 'Deposit Successful',
            'data' => $data
        ]);
    }
}
