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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constraints('users')->onDelete('cascade');
            $table->string('invoice')->unique();
            $table->string('method')->nullable();
            $table->string('pay_code')->nullable();
            $table->string('pay_url')->nullable();
            $table->string('checkout_url')->nullable();
            $table->double('nominal');
            $table->double('total');
            $table->double('fee');
            $table->double('amount_received');
            $table->enum('status', ['paid', 'unpaid', 'failed'])->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
