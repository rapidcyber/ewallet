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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('sender');
            $table->morphs('recipient');
            $table->string('txn_no', 12)->unique();
            $table->string('ref_no', 36)->comment('DO NOT make this unique as for invoices, we use the same invoice number as the reference');
            $table->string('currency', 5);
            $table->decimal('amount', 12, 2);
            $table->decimal('service_fee', 9, 2);
            $table->foreignId('transaction_provider_id')->constrained();
            $table->foreignId('transaction_channel_id')->constrained();
            $table->foreignId('transaction_type_id')->constrained();
            $table->decimal('rate', 6, 3)->default(1);
            $table->text('extras')->nullable();
            $table->foreignId('transaction_status_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
