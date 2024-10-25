<?php

namespace App\Http\Controllers;

use App\Helpers\TripayHelper;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\RegistrationState;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramBotController extends Controller
{
    protected $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function webhook(Request $request)
    {
        $response = $this->telegram->getWebhookUpdate();
        if (isset($response->message) && isset($response->message->text)) {
            $message = strtolower($response->message->text);
            $messageId = $response->message->message_id;
            $chatId = $response->message->chat->id;

            $user = $this->checkUserByChatId($chatId);

            $registrationState = RegistrationState::where('chat_id', $chatId)->first();

            if ($message === 'batal') {
                if ($registrationState) {
                    $registrationState->delete();
                }

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Pendaftaran berhasil dibatalkan.',
                    'reply_to_message_id' => $messageId,
                ]);

                return;
            }

            if ($registrationState && $registrationState->expires_at && Carbon::now()->greaterThan($registrationState->expires_at)) {
                $registrationState->delete();
                $this->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Pendaftaran anda sudah kadaluarsa. Silakan ketik /daftar untuk memulai kembali.',
                    'reply_to_message_id' => $messageId,
                ]);
                return;
            }

            if ($message === '/daftar') {
                RegistrationState::updateOrCreate(
                    ['chat_id' => $chatId],
                    ['step' => 'name', 'expires_at' => Carbon::now()->addMinutes(5)]
                );

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Selamat datang! Silakan masukkan nama Anda:',
                    'reply_to_message_id' => $messageId,
                ]);
            } elseif ($registrationState && $registrationState->step === 'name') {
                $registrationState->update(['name' => $message, 'step' => 'phone', 'expires_at' => Carbon::now()->addMinutes(5)]);

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Terima kasih! Sekarang, masukkan nomor telepon Anda:',
                    'reply_to_message_id' => $messageId,
                ]);
            } elseif ($registrationState && $registrationState->step === 'phone') {
                $registrationState->update(['phone' => $message, 'step' => 'shop_name', 'expires_at' => Carbon::now()->addMinutes(5)]);

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Nomor telepon diterima. Silakan masukkan nama toko Anda:',
                    'reply_to_message_id' => $messageId,
                ]);
            } elseif ($registrationState && $registrationState->step === 'shop_name') {
                $registrationState->update(['shop_name' => $message]);

                // Simpan ke tabel `users`
                User::create([
                    'chat_id' => $chatId,
                    'name' => $registrationState->name,
                    'phone' => $registrationState->phone,
                    'shop_name' => $message,
                    'status' => 'active',
                ]);

                $registrationState->delete();

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Pendaftaran selesai! Terima kasih telah mendaftar.',
                    'reply_to_message_id' => $messageId,
                ]);
            } else {
                if ($user) {
                    if ($message === '/menu' || $message === '/start') {
                        $keyboard = Keyboard::make()
                            ->row(
                                [
                                    Keyboard::button('Cek Saldo'),
                                    Keyboard::button('Bantuan'),
                                ]
                            );

                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Pilih menu:',
                            'reply_markup' => $keyboard,
                            'reply_to_message_id' => $messageId,
                        ]);
                    } else if ($message === 'cek saldo') {
                        $saldo = 'Rp.' . number_format($user->saldo, 0, '.', '.'); // Ambil nomor dari user yang terdaftar

                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => "Saldo Anda adalah $saldo.",
                            'reply_to_message_id' => $messageId,
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [
                                        ['text' => 'Deposit', 'callback_data' => 'deposit'],
                                        ['text' => 'Riwayat Deposit', 'callback_data' => 'riwayat deposit'],
                                    ]
                                ]
                            ])
                        ]);
                    } else if ($message === 'deposit') {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Silakan kirim jumlah deposit dengan format: deposit.<nominal>. Contoh: deposit.10000',
                            'reply_to_message_id' => $messageId,
                        ]);
                    } else if (strpos($message, 'deposit.') === 0) {
                        $nominal = substr($message, strlen('deposit.')); // Mengambil substring setelah 'deposit.'
                        $user = User::where('chat_id', $chatId)->first();
                        $pendingDeposit = Deposit::where('user_id', $user->id)->where('status', 'unpaid')->exists();

                        if ($pendingDeposit) {
                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => 'Anda masih memiliki deposit yang belum dibayar. Silakan selesaikan deposit tersebut sebelum melakukan deposit baru.',
                                'reply_to_message_id' => $messageId,
                            ]);
                            return;
                        }

                        if (is_numeric($nominal) && $nominal >= 10000) {
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

                            $response = TripayHelper::createDepositLocal($nominal, 'QRIS2', $orderItems, $chatId);

                            if (!$response->success) {
                                $this->telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => 'Gagal Deposit',
                                    'reply_to_message_id' => $messageId,
                                ]);

                                return;
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

                            $nominalFormatted = number_format($result->nominal, 0, '.', '.');
                            $feeFormatted = number_format($result->fee, 0, '.', '.');
                            $totalFormatted = number_format($result->total, 0, '.', '.');
                            $amountReceivedFormatted = number_format($result->amount_received, 0, '.', '.');
                            $exp = $result->expired_at->format('d-m-Y H:i:s');

                            $pay = '';

                            if ($result->pay_code) {
                                $pay = "Kode Pembayaran: $result->pay_code";
                            } else if ($result->pay_url) {
                                $pay = "Link Pembayaran: $result->pay_url";
                            } else {
                                $pay = "Checkout Pembayaran: $result->checkout_url";
                            }

                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => "Hallo, $user->name.\nTerima Kasih telah melakukan deposit.\nDetail Deposit:\n\n- Invoice: $result->invoice.\n- Metode: $result->method.\n- Nominal: $nominalFormatted.\n- Fee: $feeFormatted.\n- Total Harus Dibayarkan: $totalFormatted.\n- Saldo Diterima: $amountReceivedFormatted.\n- Expired: $exp.\n\n$pay\n\nHarap Dibayarkan sebelum waktu expired\nUntuk batal deposit ketik: batal.deposit.<invoice>\n\nTerima Kasih.",
                                'reply_to_message_id' => $messageId,
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            ['text' => 'Bayar Sekarang', 'url' => $result->checkout_url],
                                            ['text' => 'Cancel Deposit', 'callback_data' => 'cancel.deposit.' . $result->invoice],
                                        ]
                                    ]
                                ])
                            ]);
                            return;
                        } else if ($nominal < 10000) {
                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => 'Minimal Deposit Rp.10.000',
                                'reply_to_message_id' => $messageId,
                            ]);

                            return;
                        } else {
                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => 'Nominal tidak valid. Silakan kirim dengan format: deposit.<nominal>',
                                'reply_to_message_id' => $messageId,
                            ]);
                            return;
                        }
                    } else if (strpos($message, 'batal.deposit.') === 0) {
                        $invoice = substr($message, strlen('batal.deposit.')); // Mengambil substring setelah 'batal.deposit.'

                        $deposit = Deposit::where('invoice', $invoice)->where('status', 'unpaid')->first();

                        if ($deposit) {
                            $deposit->status = 'failed';
                            $deposit->save();

                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => 'Deposit berhasil dibatalkan.',
                                'reply_to_message_id' => $messageId,
                            ]);
                        } else {
                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => 'Deposit yang Anda cari tidak ditemukan.',
                                'reply_to_message_id' => $messageId,
                            ]);
                        }
                    } else {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Perintah tidak dikenali.',
                            'reply_to_message_id' => $messageId,
                        ]);
                    }
                } else {
                    // Jika pengguna tidak terdaftar di database
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Nomor Anda tidak terdaftar di sistem kami. Silakan ketik /daftar untuk mendaftar.',
                        'reply_to_message_id' => $messageId,
                    ]);
                }
            }
        }

        if (isset($response['callback_query'])) {
            $callbackQuery = $response['callback_query'];
            $callbackData = $callbackQuery['data'];
            $chatId = $callbackQuery['message']['chat']['id'];
            $messageId = $callbackQuery['message']['message_id'];

            $user = $this->checkUserByChatId($chatId);

            // Tangani callback_data yang diterima
            if ($callbackData === 'deposit') {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Silakan kirim jumlah deposit dengan format: deposit.<nominal>. Contoh: deposit.10000',
                    'reply_to_message_id' => $messageId,
                ]);
            } else if ($callbackData === 'riwayat deposit') {
                // Membuat menu riwayat deposit
                $deposits = Deposit::where('user_id', $user->id)->latest()->limit(10)->get();

                $replyMarkup = $this->generateRiwayatDeposit($deposits);

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Berikut Riwayat Deposit Kamu, Dari yang terbaru sampai terlama',
                    'reply_to_message_id' => $messageId,
                    'reply_markup' => $replyMarkup,
                ]);
            } else if (strpos($callbackData, 'detail_deposit.') === 0) {
                $invoice = substr($callbackData, strlen('detail_deposit.'));
                $deposit = Deposit::where('invoice', $invoice)->first();

                if ($deposit) {
                    $nominalFormatted = number_format($deposit->nominal, 0, '.', '.');
                    $feeFormatted = number_format($deposit->fee, 0, '.', '.');
                    $totalFormatted = number_format($deposit->total, 0, '.', '.');
                    $amountReceivedFormatted = number_format($deposit->amount_received, 0, '.', '.');
                    $exp = $deposit->expired_at->format('d-m-Y H:i:s');
                    $paidAt = $deposit->paid_at ? $deposit->paid_at->format('d-m-Y H:i:s') : null;
                    $cancelAt = $deposit->updated_at->format('d-m-Y H:i:s');

                    $pay = '';

                    if ($deposit->pay_code) {
                        $pay = "Kode Pembayaran: $deposit->pay_code";
                    } else if ($deposit->pay_url) {
                        $pay = "Link Pembayaran: $deposit->pay_url";
                    } else {
                        $pay = "Checkout Pembayaran: $deposit->checkout_url";
                    }

                    if ($deposit->status === 'unpaid') {
                        $messageRes = "Detail Deposit:\n\n" .
                            "- Invoice: $deposit->invoice.\n" .
                            "- Metode: $deposit->method.\n" .
                            "- Nominal: $nominalFormatted.\n" .
                            "- Fee: $feeFormatted.\n" .
                            "- Total Harus Dibayarkan: $totalFormatted.\n" .
                            "- Saldo Diterima: $amountReceivedFormatted.\n" .
                            "- Expired: $exp.\n\n" .
                            "$pay\n\n" .
                            "Harap Dibayarkan sebelum waktu expired\n\n" .
                            "Terima Kasih.";

                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $messageRes,
                            'reply_to_message_id' => $messageId,
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [
                                        ['text' => 'Bayar Sekarang', 'url' => $deposit->checkout_url],
                                        ['text' => 'Cancel Deposit', 'callback_data' => 'cancel.deposit.' . $deposit->invoice],
                                    ]
                                ]
                            ])
                        ]);
                    } else if ($deposit->status === 'paid') {
                        $messageRes = "Detail Deposit:\n\n" .
                            "- Invoice: $deposit->invoice.\n" .
                            "- Metode: $deposit->method.\n" .
                            "- Nominal: $nominalFormatted.\n" .
                            "- Fee: $feeFormatted.\n" .
                            "- Total Harus Dibayarkan: $totalFormatted.\n" .
                            "- Saldo Diterima: $amountReceivedFormatted.\n" .
                            "- Dibayar Pada: $paidAt.\n\n" .
                            "$pay\n\n" .
                            "Terima Kasih.";

                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $messageRes,
                            'reply_to_message_id' => $messageId,
                        ]);
                    } else {
                        $messageRes = "Detail Deposit:\n\n" .
                            "- Invoice: $deposit->invoice.\n" .
                            "- Metode: $deposit->method.\n" .
                            "- Nominal: $nominalFormatted.\n" .
                            "- Fee: $feeFormatted.\n" .
                            "- Total Harus Dibayarkan: $totalFormatted.\n" .
                            "- Saldo Diterima: $amountReceivedFormatted.\n" .
                            "- Dibatalkan Pada: $cancelAt.\n\n" .
                            "$pay\n\n" .
                            "Terima Kasih.";

                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $messageRes,
                            'reply_to_message_id' => $messageId,
                        ]);
                    }
                } else {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Deposit tidak ditemukan.',
                        'reply_to_message_id' => $messageId,
                    ]);
                }
            } else if (strpos($callbackData, 'cancel.deposit.') === 0) {
                $invoice = substr($callbackData, strlen('cancel.deposit.')); // Mengambil substring setelah 'cancel.deposit.'

                $deposit = Deposit::where('invoice', $invoice)->where('status', 'unpaid')->first();

                if ($deposit) {
                    $deposit->status = 'failed';
                    $deposit->save();

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Deposit berhasil dibatalkan.',
                        'reply_to_message_id' => $messageId,
                    ]);
                } else {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Deposit yang Anda cari tidak ditemukan.',
                        'reply_to_message_id' => $messageId,
                    ]);
                }
            }

            // Jangan lupa untuk mengirimkan callback_query response (wajib)
            try {
                // Kirim pesan atau penanganan callback query
                $this->telegram->answerCallbackQuery([
                    'callback_query_id' => $callbackQuery['id'],
                    'text' => 'Permintaan diproses',
                ]);

                // Lanjutkan dengan penanganan lainnya (misalnya kirim riwayat deposit)
            } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
                // Tangkap error dan kirim pesan kepada pengguna
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Maaf, terjadi kesalahan atau permintaan Anda telah kedaluwarsa. Silakan coba lagi.',
                ]);
            }
        }
    }


    private function checkUserByChatId($chatId)
    {
        // Cari pengguna di database berdasarkan chatId
        return User::where('chat_id', $chatId)->first();
    }

    public function generateRiwayatDeposit($deposits)
    {
        if ($deposits->isEmpty()) {
            return json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'Tidak ada riwayat deposit', 'callback_data' => 'no_data']
                    ]
                ]
            ]);
        }

        $keyboard = [];
        foreach ($deposits as $deposit) {
            $keyboard[] = [
                ['text' => $deposit->invoice . ' - Rp.' . number_format($deposit->total, 0, '.', '.') . ' - (' . $deposit->status . ')', 'callback_data' => 'detail_deposit.' . $deposit->invoice]
            ];
        };

        return json_encode([
            'inline_keyboard' => $keyboard
        ]);
    }
}
