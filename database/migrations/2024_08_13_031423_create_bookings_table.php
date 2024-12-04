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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->foreignId('service_id')->constrained();
            $table->json('slots');
            $table->date('service_date');
            $table->text('message');
            $table->foreignId('invoice_id')->nullable()->constrained();
            $table->foreignId('booking_status_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
