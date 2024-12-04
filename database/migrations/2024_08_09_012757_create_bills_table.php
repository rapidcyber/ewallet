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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->string('ref_no', 18)->unique();
            $table->string('receipt_email')->nullable();
            $table->string('biller_code');
            $table->string('biller_name');
            $table->json('infos');
            $table->integer('remind_date')->nullable();
            $table->decimal('amount', 11, 2);
            $table->string('currency', 3)->default('PHP');
            $table->timestamp('payment_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
