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
        Schema::create('registration_states', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique();
            $table->string('step')->nullable();  // Menyimpan langkah pendaftaran saat ini
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('shop_name')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_states');
    }
};
