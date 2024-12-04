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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->morphs('sender');
            $table->morphs('recipient'); // Merchant can send invoice to user and merchants
            $table->string('invoice_no');
            $table->string('currency')->default('PHP');
            $table->string('message');
            $table->date('due_date');
            $table->enum('status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->decimal('minimum_partial', 11, 2)->nullable();
            $table->enum('type', ['payable', 'quotation'])->default('payable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
