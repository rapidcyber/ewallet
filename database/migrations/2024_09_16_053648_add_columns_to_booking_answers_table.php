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
        Schema::table('booking_answers', function (Blueprint $table) {
            $table->enum('type', ['dropdown', 'paragraph', 'multiple', 'checkbox']);
            $table->boolean('is_important')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_answers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('is_important');
        });
    }
};
