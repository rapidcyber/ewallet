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
        Schema::create('product_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_order_id')->constrained()->cascadeOnDelete();
            $table->boolean('awb_downloaded')->default(false);
            $table->boolean('packing_list_downloaded')->default(false);
            $table->boolean('pick_list_downloaded')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_order_documents');
    }
};
