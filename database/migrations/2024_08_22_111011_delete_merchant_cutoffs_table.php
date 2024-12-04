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
        if (Schema::hasColumn('merchants', 'merchant_cutoff_id')) {
            Schema::table('merchants', function (Blueprint $table) {
                $table->dropForeign(['merchant_cutoff_id']);
                $table->dropColumn('merchant_cutoff_id');
            });
        }
        Schema::dropIfExists('merchant_cutoffs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('merchant_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);
            $table->string('slug', 20)->unique();
        });
        Schema::table('merchants', function (Blueprint $table) {
            $table->foreignId('merchant_cutoff_id');
        });
    }
};
