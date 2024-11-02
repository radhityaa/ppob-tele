<?php

namespace App\Http\Controllers;

use App\Helpers\DigiflazzHelper;
use App\Models\Digiflazz;
use App\Models\Margin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

use function PHPUnit\Framework\isEmpty;

class DigiflazzController extends Controller
{
    protected $telegram;

    public function __construct(Api $telegram)
    {
        return $this->telegram = $telegram;
    }

    public function getProducts()
    {
        $settingMargin = Margin::first();
        $margin = $settingMargin->margin;

        $result = DigiflazzHelper::getService('prepaid');

        if (isset($result->data)) {
            // Jika respons mengandung kesalahan
            if (isset($result->data->rc) && $result->data->rc !== "00") {
                return response()->json([
                    'success' => false,
                    'message' => $result->data->message ?? 'Terjadi kesalahan saat mengambil data dari Digiflazz.',
                ], 400);
            }

            // Jika respons berhasil
            if (is_array($result->data)) {
                $digiflazzSkuCodes = [];

                foreach ($result->data as $item) {
                    $digiflazzSkuCodes[] = $item->buyer_sku_code;

                    // Cari produk berdasarkan kriteria yang diberikan
                    $existingProduct = Digiflazz::where('buyer_sku_code', $item->buyer_sku_code)
                        ->where('price', $item->price + $margin)
                        ->where('buyer_product_status', $item->buyer_product_status)
                        ->where('seller_product_status', $item->seller_product_status)
                        ->where('stock', $item->stock)
                        ->first();

                    // Jika data tidak ditemukan (atau ada perbedaan), lakukan update atau insert
                    if (!$existingProduct) {
                        Digiflazz::create(
                            [
                                'buyer_sku_code' => $item->buyer_sku_code,
                                'product_name' => $item->product_name,
                                'category' => $item->category,
                                'brand' => $item->brand,
                                'type' => $item->type,
                                'price' => $item->price + $margin,
                                'seller_name' => $item->seller_name,
                                'buyer_product_status' => $item->buyer_product_status,
                                'seller_product_status' => $item->seller_product_status,
                                'unlimited_stock' => $item->unlimited_stock,
                                'stock' => $item->stock,
                                'multi' => $item->multi,
                                'start_cut_off' => $item->start_cut_off,
                                'end_cut_off' => $item->end_cut_off,
                                'desc' => $item->desc,
                            ]
                        );
                    }
                }

                // Hapus produk yang tidak ada dalam respons terbaru
                Digiflazz::whereNotIn('buyer_sku_code', $digiflazzSkuCodes)->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil mengambil data.',
                ], 200);
            }
        } else {
            // Jika respons tidak memiliki data sama sekali
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dari Digiflazz.',
            ], 400);
        }
    }

    public function updateProducts()
    {
        $settingMargin = Margin::first();
        $margin = $settingMargin->margin;

        $result = DigiflazzHelper::getService('prepaid');

        if (isset($result->data)) {
            // Jika respons mengandung kesalahan
            if (isset($result->data->rc) && $result->data->rc !== "00") {
                return response()->json([
                    'success' => false,
                    'message' => $result->data->message ?? 'Terjadi kesalahan saat mengambil data dari Digiflazz.',
                ], 400);
            }

            // Jika respons berhasil
            if (is_array($result->data)) {
                $digiflazzSkuCodes = [];
                $updatedProducts = [];
                $insertedProducts = [];

                foreach ($result->data as $item) {
                    $digiflazzSkuCodes[] = $item->buyer_sku_code;

                    $existingProduct = Digiflazz::where('buyer_sku_code', $item->buyer_sku_code)->first();

                    if ($existingProduct) {
                        $shouldUpdate = (
                            $existingProduct->price != ($item->price + $margin) ||
                            $existingProduct->seller_product_status != $item->seller_product_status ||
                            $existingProduct->stock != $item->stock
                        );

                        if ($shouldUpdate) {
                            // Update produk yang sudah ada
                            $existingProduct->update([
                                'product_name' => $item->product_name,
                                'category' => $item->category,
                                'brand' => $item->brand,
                                'type' => $item->type,
                                'price' => $item->price + $margin,
                                'seller_name' => $item->seller_name,
                                'buyer_product_status' => $item->buyer_product_status,
                                'seller_product_status' => $item->seller_product_status,
                                'unlimited_stock' => $item->unlimited_stock,
                                'stock' => $item->stock,
                                'multi' => $item->multi,
                                'start_cut_off' => $item->start_cut_off,
                                'end_cut_off' => $item->end_cut_off,
                                'desc' => $item->desc,
                            ]);

                            // Masukkan data yang di-update ke array
                            $updatedProducts[] = [
                                'buyer_sku_code' => $item->buyer_sku_code,
                                'product_name' => $item->product_name,
                                'category' => $item->category,
                                'brand' => $item->brand,
                                'type' => $item->type,
                                'price' => number_format($item->price + $margin, 0, '.', '.'),
                                'seller_product_status' => $item->seller_product_status,
                            ];
                        }
                    } else {
                        // Tambahkan produk baru
                        $newProduct = Digiflazz::create([
                            'buyer_sku_code' => $item->buyer_sku_code,
                            'product_name' => $item->product_name,
                            'category' => $item->category,
                            'brand' => $item->brand,
                            'type' => $item->type,
                            'price' => $item->price + $margin,
                            'seller_name' => $item->seller_name,
                            'buyer_product_status' => $item->buyer_product_status,
                            'seller_product_status' => $item->seller_product_status,
                            'unlimited_stock' => $item->unlimited_stock,
                            'stock' => $item->stock,
                            'multi' => $item->multi,
                            'start_cut_off' => $item->start_cut_off,
                            'end_cut_off' => $item->end_cut_off,
                            'desc' => $item->desc,
                        ]);

                        // Masukkan data yang di-insert ke array
                        $insertedProducts[] = [
                            'buyer_sku_code' => $item->buyer_sku_code,
                            'product_name' => $item->product_name,
                            'category' => $item->category,
                            'brand' => $item->brand,
                            'price' => number_format($item->price + $margin, 0, '.', '.'),
                            'seller_product_status' => $item->seller_product_status,
                        ];
                    }
                }

                Digiflazz::whereNotIn('buyer_sku_code', $digiflazzSkuCodes)->delete();

                // Kirim notifikasi ke Telegram
                // $date = now()->locale('id')->translatedFormat('l, d F Y');

                // if (!empty($updatedProducts)) {
                //     $messageUpdate = "Update produk tanggal " . $date . "\n\n";
                //     foreach ($updatedProducts as $product) {
                //         $status = $product['seller_product_status'] ? 'Normal' : 'Gangguan';
                //         $messageUpdate .= "- " . $product['category'] . " " . $product['brand'] . "\n- Kode: " . $product['buyer_sku_code'] . "\n- Produk: " . $product['product_name'] . "\n- Harga: " . $product['price'] . "\n- Status: " . $status . "\n\n";
                //     }

                //     $this->telegram->sendMessage([
                //         'chat_id' => env('TELEGRAM_CHANNEL_ID'),
                //         'text' => $messageUpdate
                //     ]);
                // }

                // if (!empty($insertedProducts)) {
                //     $messageInsert = "Produk baru per tanggal " . $date . "\n\n";
                //     foreach ($insertedProducts as $product) {
                //         $status = $product['seller_product_status'] ? 'Normal' : 'Gangguan';
                //         $messageInsert .= "- " . $product['category'] . " " . $product['brand'] . "\n- Kode: " . $product['buyer_sku_code'] . "\n- Produk: " . $product['product_name'] . "\n- Harga: " . $product['price'] . "\n- Status: " . $status . "\n\n";
                //     }

                //     $this->telegram->sendMessage([
                //         'chat_id' => env('TELEGRAM_CHANNEL_ID'),
                //         'text' => $messageInsert
                //     ]);
                // }

                return response()->json([
                    'success' => true,
                    'message' => 'Data produk berhasil diperbarui.',
                    'updated_products' => $updatedProducts,
                    'inserted_products' => $insertedProducts,
                ], 200);
            }
        } else {
            // Jika respons tidak memiliki data sama sekali
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat updated data dari Digiflazz.',
            ], 400);
        }
    }
}
