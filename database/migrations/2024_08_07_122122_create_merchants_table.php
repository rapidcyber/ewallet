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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->uuid('app_id');
            $table->unsignedBigInteger('account_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_category_id')->constrained();
            $table->string('name', 50);
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('phone_iso', 5);
            $table->string('phone_number', 20);
            $table->string('landline_iso', 5)->nullable();
            $table->string('landline_number', 20)->nullable();
            $table->string('invoice_prefix', 5)->unique();
            $table->enum('status', ['pending', 'verified', 'rejected', 'deactivated']);
            $table->enum('apply_for_realholmes', ['merchant', 'owner'])->nullable()->default(null);
            $table->foreignId('merchant_cutoff_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
