<?php

namespace App\Http\Controllers;

use App\Helpers\TripayHelper;
use App\Models\Deposit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CallbackController extends Controller
{
    public function callbackTripay(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, TripayHelper::getPrivateKey());

        if ($signature !== (string) $callbackSignature) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid Signature',
            ]);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = json_decode($json);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }

        $depositInvoice = $data->merchant_ref;
        $status = strtoupper((string) $data->status);

        if ($data->is_closed_payment === 1) {
            $deposit = Deposit::where('invoice', $depositInvoice)->where('status', '=', 'unpaid')->first();

            if (!$deposit) {
                return Response::json([
                    'success' => false,
                    'message' => 'No invoice found or already paid: ' . $depositInvoice,
                ]);
            }

            switch ($status) {
                case 'PAID':
                    $deposit->update([
                        'status' => 'paid',
                        'paid_at' => Carbon::createFromTimestamp($data->paid_at)->toDateTimeString()
                    ]);
                    $user = User::where('id', $deposit->user_id)->first();

                    $user->update([
                        'saldo' => $user->saldo + $data->amount_received
                    ]);
                    break;

                case 'EXPIRED':
                    $deposit->update(['status' => 'failed']);
                    break;

                case 'FAILED':
                    $deposit->update(['status' => 'failed']);
                    break;

                default:
                    return Response::json([
                        'success' => false,
                        'message' => 'Unrecognized payment status',
                    ]);
            }

            return Response::json(['success' => true]);
        }
    }
}
