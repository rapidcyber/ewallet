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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('recipient');
            $table->string('ref_id');
            $table->enum('type', ['alert', 'message'])->default('alert');
            $table->enum('status', ['read', 'unread'])->default('unread');
            $table->foreignId('notification_module_id')->constrained();
            $table->text('message');
            $table->json('extras')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
