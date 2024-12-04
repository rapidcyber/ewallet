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
        Schema::table('user_kycs', function (Blueprint $table) {
            $table->string('request_id')->after('user_id')->comment('This is used to validate the user flow from the TS server');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_kycs', function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
    }
};
