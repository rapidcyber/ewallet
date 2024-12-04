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
        Schema::create('ecpay_webhook_data', function (Blueprint $table) {
            $table->id();
            $table->json('data')->comment('This is the data passed from the ECPay Server');
            $table->string('env');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecpay_webhook_data');
    }
};
