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
        Schema::create('share_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('decision_lists')->onDelete('cascade');
            $table->string('code', 8)->unique(); // 8 characters should be sufficient for unique codes
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('list_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_codes');
    }
}; 