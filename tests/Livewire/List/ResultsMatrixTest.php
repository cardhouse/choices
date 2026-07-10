<?php

namespace Tests\Livewire\List;

use App\Livewire\List\ResultsMatrix;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;
use Tests\TestCase;

class ResultsMatrixTest extends TestCase
{
    public function test_displays_matrix_with_results()
    {
        $list = DecisionList::factory()->create();

        [$item1, $item2, $item3] = DecisionListItem::factory()->count(3)->create(['list_id' => $list->id]);

        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $item1->id,
            'item_b_id' => $item2->id,
        ]);

        Vote::create([
            'matchup_id' => $matchup->id,
            'user_id' => $list->user_id,
            'chosen_item_id' => $item1->id,
        ]);

        Livewire::test(ResultsMatrix::class, ['list' => $list])
            ->assertSee($item1->label)
            ->assertSee($item2->label)
            ->assertSee($item3->label)
            ->assertSee('✅')
            ->assertSee('❌')
            ->assertSee('-');
    }

    public function test_displays_vote_counts_and_ties_when_enabled()
    {
        $list = DecisionList::factory()->create();

        [$item1, $item2] = DecisionListItem::factory()->count(2)->create(['list_id' => $list->id]);

        $matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $item1->id,
            'item_b_id' => $item2->id,
        ]);

        // Two voters split the matchup: a tie with counts shown.
        [$alice, $bob] = User::factory()->count(2)->create();
        Vote::create(['matchup_id' => $matchup->id, 'user_id' => $alice->id, 'chosen_item_id' => $item1->id]);
        Vote::create(['matchup_id' => $matchup->id, 'user_id' => $bob->id, 'chosen_item_id' => $item2->id]);

        Livewire::test(ResultsMatrix::class, ['list' => $list, 'showVoteCounts' => true])
            ->assertSee('= (1–1)');
    }
}
