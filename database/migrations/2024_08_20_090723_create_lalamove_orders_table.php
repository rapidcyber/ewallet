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
        Schema::create('lalamove_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lalamove_service_id')->constrained();
            $table->string('order_id');
            $table->text('share_link');
            $table->string('status');
            $table->string('previous_status');
            $table->foreignId('lalamove_driver_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lalamove_orders');
    }
};
