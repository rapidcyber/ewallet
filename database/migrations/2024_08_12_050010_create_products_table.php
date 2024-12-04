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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained();
            $table->string('sku', 12);
            $table->unique(['merchant_id', 'sku']);
            $table->foreignId('product_category_id')->constrained();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->json('variations')->nullable();
            $table->decimal('price', 15, 2);
            $table->unsignedBigInteger('stock_count')->default(0);
            $table->decimal('sale_amount', 2, 2)->default(0);
            $table->unsignedBigInteger('sold_count')->default(0);
            $table->foreignId('product_condition_id')->constrained();
            $table->boolean('on_demand')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(false);
            $table->enum('approval_status', ['review', 'approved', 'rejected', 'suspended'])->default('review');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
