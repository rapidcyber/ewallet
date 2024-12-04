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
            $table->decimal('price')->after('expires_at');
            $table->morphs('buyer');
            $table->foreignId('product_id')->constrained()->after('price');
            $table->string('buyer_stop_id')->after('product_id');
            $table->string('seller_stop_id')->after('buyer_stop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lalamove_services', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropMorphs('buyer');
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->dropColumn('buyer_stop_id');
            $table->dropColumn('seller_stop_id');
        });
    }
};
