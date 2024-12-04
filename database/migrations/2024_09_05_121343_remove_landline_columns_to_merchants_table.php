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
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn('landline_iso');
            $table->dropColumn('landline_number');
            $table->dropColumn('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->string('landline_iso')->nullable();
            $table->string('landline_number')->nullable();
            $table->string('website');
        });
    }
};
