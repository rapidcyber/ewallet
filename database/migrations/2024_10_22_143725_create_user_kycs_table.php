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
        Schema::create('user_kycs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->double('liveness_score')->default(0);
            $table->double('card_sanity_score')->default(0);
            $table->double('selfie_sanity_score')->default(0);
            $table->double('card_tampering_score')->default(0);
            $table->double('compare_face_score')->default(0);

            $table->string('liveness_req_id')->default('');
            $table->string('card_sanity_req_id')->default('');
            $table->string('selfie_sanity_req_id')->default('');
            $table->string('card_tampering_req_id')->default('');
            $table->string('compare_face_req_id')->default('');

            $table->string('selfie_image_id')->default('');
            $table->string('front_card_image_id')->default('');
            $table->string('back_card_image_id')->default('');

            $table->json('card_info')->nullable();
            $table->string('card_info_req_id')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_kycs');
    }
};
