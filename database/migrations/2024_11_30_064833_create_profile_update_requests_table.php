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
        Schema::create('profile_update_requests', function (Blueprint $table) {
            $table->id();

            /// user kycs
            $table->foreignId('user_id');
            $table->string('request_id')->comment('This is used to validate the user flow from the TS server');

            $table->double('liveness_score')->default(0);
            $table->double('card_sanity_score')->default(0);
            $table->double('selfie_sanity_score')->default(0);
            $table->double('card_tampering_score')->default(0);

            $table->string('liveness_req_id')->default('');
            $table->string('card_sanity_req_id')->default('');
            $table->string('selfie_sanity_req_id')->default('');
            $table->string('card_tampering_req_id')->default('');

            $table->string('selfie_image_id')->default('');
            $table->string('front_card_image_id')->default('');
            $table->string('back_card_image_id')->default('');

            /// profile table
            $table->string('first_name', 120)->nullable();
            $table->string('middle_name', 120)->nullable();
            $table->string('surname', 120)->nullable();
            $table->string('suffix', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_update_requests');
    }
};
