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
        Schema::create('product_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->morphs('buyer');
            $table->decimal('amount');
            $table->string('order_number', 20);
            $table->foreignId('shipping_option_id')->constrained();
            $table->foreignId('payment_option_id')->constrained();
            $table->string('tracking_number', 16);
            $table->foreignId('shipping_status_id')->constrained();
            $table->timestamp('processed_at')->nullable();
            $table->text('termination_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_orders');
    }
};
