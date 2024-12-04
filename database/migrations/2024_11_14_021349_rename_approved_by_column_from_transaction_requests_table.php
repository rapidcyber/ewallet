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
        Schema::table('transaction_requests', function (Blueprint $table) {
            $table->renameColumn('approved_by', 'processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_requests', function (Blueprint $table) {
            $table->renameColumn('processed_by', 'approved_by');
        });
    }
};
