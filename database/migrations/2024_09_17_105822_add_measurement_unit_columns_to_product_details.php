<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->enum('mass_unit', ['mg', 'kg', 't'])
                ->default('kg')
                ->after('height');

            $table->enum('length_unit', ['mm', 'cm', 'm', 'inch', 'ft', 'yd'])
                ->default('cm')
                ->after('mass_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropColumn('mass_unit');
            $table->dropColumn('length_unit');
        });
    }
};
