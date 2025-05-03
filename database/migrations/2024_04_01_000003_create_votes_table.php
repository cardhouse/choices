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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchup_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_token')->nullable();
            $table->foreignId('chosen_item_id')->constrained('items')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('matchup_id');
            $table->index('user_id');
            $table->index('session_token');
            $table->index('chosen_item_id');
            $table->index('ip_address');

            // Ensure either user_id or session_token is present
            $table->unique(['matchup_id', 'user_id'], 'unique_user_vote');
            $table->unique(['matchup_id', 'session_token'], 'unique_session_vote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
}; 