<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Votes are now the source of truth for matchup outcomes, so matchups no
     * longer carry a global winner or completion status.
     */
    public function up(): void
    {
        // SQLite cannot drop columns bound by CHECK/FK constraints (the old
        // status enum and winner FK), so the table is rebuilt instead.
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildMatchupsTableForSqlite();

            return;
        }

        Schema::table('matchups', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropForeign(['winner_item_id']);
        });

        Schema::table('matchups', function (Blueprint $table) {
            $table->dropColumn(['winner_item_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matchups', function (Blueprint $table) {
            $table->foreignId('winner_item_id')->nullable()->constrained('decision_list_items')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->index('status');
        });
    }

    private function rebuildMatchupsTableForSqlite(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::create('matchups_rebuild', function (Blueprint $table) {
                $table->id();
                $table->foreignId('list_id')->constrained('decision_lists')->cascadeOnDelete();
                $table->foreignId('item_a_id')->constrained('decision_list_items')->cascadeOnDelete();
                $table->foreignId('item_b_id')->constrained('decision_list_items')->cascadeOnDelete();
                $table->integer('round_number')->default(1);
                $table->timestamps();
            });

            DB::statement(
                'INSERT INTO matchups_rebuild (id, list_id, item_a_id, item_b_id, round_number, created_at, updated_at)
                 SELECT id, list_id, item_a_id, item_b_id, round_number, created_at, updated_at FROM matchups'
            );

            Schema::drop('matchups');
            Schema::rename('matchups_rebuild', 'matchups');

            // Indexes are added after the old table is gone; their names would
            // collide with the old table's while both exist.
            Schema::table('matchups', function (Blueprint $table) {
                $table->index('list_id', 'matchups_list_id_index');
                $table->index(['item_a_id', 'item_b_id'], 'matchups_item_a_id_item_b_id_index');
                $table->index('round_number', 'matchups_round_number_index');
                $table->unique(['list_id', 'item_a_id', 'item_b_id'], 'matchups_list_id_item_a_id_item_b_id_unique');
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
