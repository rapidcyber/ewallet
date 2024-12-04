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
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->foreignId('transaction_id')->after('invoice_id')->nullable()->constrained();
            $table->string('message')->after('transaction_id')->default('');
            $table->decimal('amount')->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->enum('status', ['paid', 'partial', 'unpaid']);
            $table->dropForeign('transaction_id');
            $table->dropColumn('transaction_id');
            $table->dropColumn('message');
            $table->dropColumn('amount');
        });
    }
};
