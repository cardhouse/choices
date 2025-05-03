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
        Schema::create('matchups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_a_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('item_b_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('winner_item_id')->nullable()->constrained('items')->onDelete('set null');
            $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending');
            $table->integer('round_number')->default(1);
            $table->timestamps();

            // Indexes
            $table->index('list_id');
            $table->index(['item_a_id', 'item_b_id']);
            $table->index('status');
            $table->index('round_number');

            // Ensure unique pairings within a list
            $table->unique(['list_id', 'item_a_id', 'item_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchups');
    }
}; 