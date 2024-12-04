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
        Schema::create('return_order_dispute_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_order_dispute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('decision_type_id');
            $table->foreign('decision_type_id')->references('id')->on('return_order_dispute_decision_types');
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_order_dispute_decisions');
    }
};
