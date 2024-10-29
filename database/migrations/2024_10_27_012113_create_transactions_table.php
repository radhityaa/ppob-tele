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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constraints('users')->onDelete('cascade');
            $table->string('invoice')->unique();
            $table->string('target');
            $table->string('buyer_sku_code');
            $table->string('product_name');
            $table->string('price');
            $table->string('customer_no')->nullable();
            $table->string('customer_name')->nullable();
            $table->double('admin')->nullable();
            $table->text('description')->nullable();
            $table->string('message')->nullable();
            $table->string('sn')->nullable();
            $table->double('selling_price')->nullable();
            $table->string('tarif')->nullable();
            $table->string('daya')->nullable();
            $table->string('billing')->nullable();
            $table->json('detail')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->default('prabayar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
