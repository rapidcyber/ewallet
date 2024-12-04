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
        Schema::table('shipping_options', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
            $table->dropUnique(['merchant_id', 'slug']);
            $table->dropColumn('merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_options', function (Blueprint $table) {
            $table->foreignId('merchant_id')->constrained();
            $table->unique(['merchant_id', 'slug']);
        });
    }
};
