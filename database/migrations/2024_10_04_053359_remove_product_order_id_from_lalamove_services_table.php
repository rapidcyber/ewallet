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
        Schema::table('lalamove_services', function (Blueprint $table) {
            $table->dropForeign(['product_order_id']);
            $table->dropColumn('product_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lalamove_services', function (Blueprint $table) {
            $table->foreignId('product_order_id')->constrained();
        });
    }
};
