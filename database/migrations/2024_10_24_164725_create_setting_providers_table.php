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
        Schema::create('setting_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->enum('mode', ['dev', 'prod'])->default('dev');
            $table->string('type');
            $table->string('api_key')->nullable();
            $table->string('private_key')->nullable();
            $table->string('code')->nullable();
            $table->string('username')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('webhook_id')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_providers');
    }
};
