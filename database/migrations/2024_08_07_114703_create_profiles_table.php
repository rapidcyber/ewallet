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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 120);
            $table->string('middle_name', 120)->nullable();
            $table->string('surname', 120);
            $table->string('suffix', 50)->nullable();
            $table->string('mother_maiden_name', 255)->nullable();
            $table->string('nationality')->nullable();
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('landline_iso', 5)->nullable();
            $table->string('landline_number', 20)->nullable();
            $table->string('photo_src')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected', 'deactivated'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
