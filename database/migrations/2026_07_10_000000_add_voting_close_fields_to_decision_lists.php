<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('decision_lists', function (Blueprint $table) {
            $table->timestamp('voting_closes_at')->nullable()->after('claimed_at');
            $table->timestamp('voting_closed_at')->nullable()->after('voting_closes_at');
        });

        // Lists that finished voting under the old single-voter flow are closed.
        DB::table('decision_lists')
            ->whereNotNull('voting_completed_at')
            ->update(['voting_closed_at' => DB::raw('voting_completed_at')]);

        Schema::table('decision_lists', function (Blueprint $table) {
            $table->dropColumn('voting_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decision_lists', function (Blueprint $table) {
            $table->timestamp('voting_completed_at')->nullable();
        });

        DB::table('decision_lists')
            ->whereNotNull('voting_closed_at')
            ->update(['voting_completed_at' => DB::raw('voting_closed_at')]);

        Schema::table('decision_lists', function (Blueprint $table) {
            $table->dropColumn(['voting_closes_at', 'voting_closed_at']);
        });
    }
};
