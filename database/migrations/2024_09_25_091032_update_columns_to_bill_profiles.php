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
        Schema::table('bill_profiles', function (Blueprint $table) {
            $table->decimal('amount')->default('0.00')->change();
            $table->enum('type', ['presentment', 'default'])->default('default')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_profiles', function (Blueprint $table) {
            $table->decimal('amount')->change();
            $table->string('type')->change();
        });
    }
};
