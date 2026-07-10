<?php

namespace Tests\Livewire\List;

use App\Livewire\List\ShowList;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\ListParticipant;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ShowListTest extends TestCase
{
    private function makeList(array $attributes = []): DecisionList
    {
        $list = DecisionList::factory()->create($attributes);
        DecisionListItem::factory()->count(2)->create(['list_id' => $list->id]);

        return $list;
    }

    public function test_owner_sees_items_and_share_panel(): void
    {
        $list = $this->makeList();

        Livewire::actingAs($list->user)
            ->test(ShowList::class, ['list' => $list])
            ->assertStatus(200)
            ->assertSee($list->title)
            ->assertSee('Share with Friends')
            ->assertSee('Start Voting');
    }

    public function test_participant_sees_list_without_share_panel(): void
    {
        $list = $this->makeList();
        $friend = User::factory()->create();
        ListParticipant::factory()->create(['list_id' => $list->id, 'user_id' => $friend->id]);

        Livewire::actingAs($friend)
            ->test(ShowList::class, ['list' => $list])
            ->assertStatus(200)
            ->assertSee($list->title)
            ->assertDontSee('Share with Friends')
            ->assertSee('Start Voting');
    }

    public function test_guest_can_view_anonymous_list(): void
    {
        $list = $this->makeList(['user_id' => null, 'is_anonymous' => true]);

        Livewire::test(ShowList::class, ['list' => $list])
            ->assertStatus(200)
            ->assertSee($list->title);
    }

    public function test_stranger_is_denied(): void
    {
        $list = $this->makeList();

        Livewire::actingAs(User::factory()->create())
            ->test(ShowList::class, ['list' => $list])
            ->assertStatus(403);

        Livewire::test(ShowList::class, ['list' => $list])
            ->assertStatus(403);
    }
}
