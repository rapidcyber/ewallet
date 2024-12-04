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
        Schema::table('return_reasons', function (Blueprint $table) {
            $table->dropForeign(['parent']);
            $table->dropColumn(['parent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_reasons', function (Blueprint $table) {
            $table->foreignId('parent')->nullable()->references('id')->on('return_reasons')->nullOnDelete();
        });
    }
};
