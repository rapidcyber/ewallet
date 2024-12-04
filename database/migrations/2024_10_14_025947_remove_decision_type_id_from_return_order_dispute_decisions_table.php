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
        Schema::table('return_order_dispute_decisions', function (Blueprint $table) {
            $table->dropForeign(['decision_type_id']);
            $table->dropColumn('decision_type_id');
            $table->text('comment')->nullable()->change();

            $table->enum('type', ['refund', 'return', 'return_and_refund', 'cancel'])->after('return_order_dispute_id');
        });

        Schema::dropIfExists('return_order_dispute_decision_types');
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('return_order_dispute_decision_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
        });

        Schema::table('return_order_dispute_decisions', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->foreignId('decision_type_id');
            $table->foreign('decision_type_id')->references('id')->on('return_order_dispute_decision_types');

            $table->text('comment')->nullable(false)->change();
        });
    }
};
