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
        Schema::create('qr_generated_data', function (Blueprint $table) {
            $table->id();
            $table->morphs('client');
            $table->string('ref_no')->unique();
            $table->string('merc_token')->nullable();
            $table->enum('type', ['static', 'dynamic']);
            $table->boolean('internal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_generated_data');
    }
};
