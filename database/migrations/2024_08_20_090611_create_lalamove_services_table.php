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
        Schema::create('lalamove_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_order_id')->constrained();
            $table->string('quotation_id');
            $table->timestamp('scheduled_at')->default(now());
            $table->timestamp('expires_at')->default(now()->addMinutes(5));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lalamove_services');
    }
};
