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
        Schema::create('transaction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained();
            $table->morphs('recipient');
            $table->string('currency', 5);
            $table->decimal('amount', 12, 2);
            $table->decimal('service_fee', 9, 2);
            $table->foreignId('transaction_provider_id')->constrained();
            $table->foreignId('transaction_channel_id')->constrained();
            $table->foreignId('transaction_type_id')->constrained();
            $table->decimal('rate', 6, 3)->default(1);
            $table->text('extras')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_requests');
    }
};
