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
        Schema::table('o_t_p_s', function (Blueprint $table) {
            $table->enum('type', ['sign_in', 'sign_up', 'change_pass', 'transaction'])
                ->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('o_t_p_s', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
