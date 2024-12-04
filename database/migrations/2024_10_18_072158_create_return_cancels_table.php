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
        Schema::create('return_cancels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('return_cancel_reason_id')->constrained();
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_cancels');
    }
};
