<?php

namespace App\Http\Controllers;

use App\Helpers\TripayHelper;
use App\Models\Deposit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Telegram\Bot\Api;

class CallbackController extends Controller
{
    protected $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function callbackTripay(Request $request)
    {
        $responseTele = $this->telegram->getWebhookUpdate();

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

                    $saldo = number_format($user->saldo, 0, '.', '.');

                    $nominalFormatted = number_format($deposit->nominal, 0, '.', '.');
                    $feeFormatted = number_format($deposit->fee, 0, '.', '.');
                    $totalFormatted = number_format($deposit->total, 0, '.', '.');
                    $amountReceivedFormatted = number_format($deposit->amount_received, 0, '.', '.');
                    $paidAt = $deposit->paid_at->format('d-m-Y H:i:s');

                    $this->telegram->sendMessage([
                        'chat_id' => $user->chat_id,
                        'text' => "Hallo, $user->name\nTerima Kasih! Pembayaran telah kami terima.\nSaldo anda sekarang: $saldo\n\nDetail Deposit:\n\n- Invoice: $deposit->invoice\n- Metode: $deposit->method\n- Total: $totalFormatted\n- Fee: $feeFormatted\n- Nominal: $nominalFormatted\n- Saldo Diterima: $amountReceivedFormatted\n- Waktu Dibayarkan: $paidAt\n- Status: paid\n\nSilahkan Cek kembali saldo anda, kirim dengan format: Cek Saldo .",
                    ]);
                    break;

                case 'EXPIRED':
                    $user = User::where('id', $deposit->user_id)->first();
                    $deposit->update(['status' => 'failed']);

                    $nominalFormatted = number_format($deposit->nominal, 0, '.', '.');
                    $feeFormatted = number_format($deposit->fee, 0, '.', '.');
                    $totalFormatted = number_format($deposit->total, 0, '.', '.');
                    $amountReceivedFormatted = number_format($deposit->amount_received, 0, '.', '.');
                    $exp = $deposit->expired_at->format('d-m-Y H:i:s');

                    $this->telegram->sendMessage([
                        'chat_id' => $user->chat_id,
                        'text' => "Hallo, $user->name.\nMohon Maaf! Pembayaran anda telah Expired atau Sudah Kadaluarsa\nSilahkan lalukan deposit ulang\n\nDetail Deposit:\n\n- Invoice: $deposit->invoice\n- Metode: $deposit->method\n- Total: $totalFormatted\n- Fee: $feeFormatted\n- Nominal: $nominalFormatted\n- Saldo Diterima: $amountReceivedFormatted\n- Expired: $exp\n- Status: failed\n\nTerima Kasih.",
                    ]);
                    break;

                case 'FAILED':
                    $user = User::where('id', $deposit->user_id)->first();
                    $deposit->update(['status' => 'failed']);

                    $nominalFormatted = number_format($deposit->nominal, 0, '.', '.');
                    $feeFormatted = number_format($deposit->fee, 0, '.', '.');
                    $totalFormatted = number_format($deposit->total, 0, '.', '.');
                    $amountReceivedFormatted = number_format($deposit->amount_received, 0, '.', '.');
                    $exp = $deposit->expired_at->format('d-m-Y H:i:s');

                    $this->telegram->sendMessage([
                        'chat_id' => $user->chat_id,
                        'text' => "Hallo, $user->name\nMohon Maaf! Pembayaran anda gagal diproses.\nSilahkan lalukan deposit ulang.\n\nDetail Deposit:\n\n- Invoice: $deposit->invoice\n- Metode: $deposit->method\n- Total: $totalFormatted\n- Fee: $feeFormatted\n- Nominal: $nominalFormatted\n- Saldo Diterima: $amountReceivedFormatted\n- Expired: $exp\n- Status: failed\n\nTerima Kasih.",
                    ]);
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