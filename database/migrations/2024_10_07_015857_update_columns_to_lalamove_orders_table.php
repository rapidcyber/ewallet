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
        Schema::table('lalamove_orders', function (Blueprint $table) {
            $table->string('previous_status')->nullable()->change();
            $table->dropForeign(['lalamove_driver_id']);
            $table->dropColumn('lalamove_driver_id');
            $table->foreignId('lalamove_driver_id')->nullable()->after('previous_status')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lalamove_orders', function (Blueprint $table) {
            $table->string('previous_status')->nullable(false)->change();
            $table->dropForeign(['lalamove_driver_id']);
            $table->dropColumn('lalamove_driver_id');
            $table->foreignId('lalamove_driver_id')->constrained();
        });
    }
};
