<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('digiflazzs', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('category');
            $table->string('brand');
            $table->string('type');
            $table->string('seller_name');
            $table->double('price');
            $table->string('buyer_sku_code');
            $table->string('buyer_product_status');
            $table->integer('seller_product_status');
            $table->string('unlimited_stock');
            $table->string('stock');
            $table->string('multi');
            $table->string('start_cut_off');
            $table->string('end_cut_off');
            $table->text('desc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digiflazzs');
    }
};
