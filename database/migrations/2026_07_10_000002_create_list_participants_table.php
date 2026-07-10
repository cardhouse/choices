<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks users who joined a shared list by redeeming a share code.
     */
    public function up(): void
    {
        Schema::create('list_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('decision_lists')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('share_code_id')->nullable()->constrained('share_codes')->nullOnDelete();
            $table->timestamps();

            $table->unique(['list_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_participants');
    }
};
