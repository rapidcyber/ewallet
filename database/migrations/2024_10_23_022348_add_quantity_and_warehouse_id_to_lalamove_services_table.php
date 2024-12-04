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
            $table->integer('quantity')->after('product_id');
            $table->foreignId('warehouse_id')->after('quantity');
            $table->timestamp('created_at')->nullable()->after('seller_stop_id')->change();
            $table->timestamp('updated_at')->nullable()->after('created_at')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lalamove_services', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('warehouse_id');
        });
    }
};
