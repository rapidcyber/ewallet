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
        Schema::create('channel_provider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('transaction_channels');
            $table->foreignId('provider_id')->constrained('transaction_providers');
            $table->decimal('service_fee', 9, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_provider');
    }
};
