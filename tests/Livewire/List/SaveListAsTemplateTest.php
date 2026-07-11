<?php

namespace Tests\Livewire\List;

use App\Livewire\List\ShowList;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\ListTemplate;
use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaveListAsTemplateTest extends TestCase
{
    #[Test]
    public function owner_can_save_their_list_as_a_template()
    {
        $user = User::factory()->create();
        $list = DecisionList::factory()->for($user)->create([
            'title' => 'Dinner Ideas',
            'description' => 'Weekly dinners',
        ]);
        DecisionListItem::factory()->for($list, 'list')->create(['label' => 'Pizza']);
        DecisionListItem::factory()->for($list, 'list')->create(['label' => 'Tacos']);

        Livewire::actingAs($user)
            ->test(ShowList::class, ['list' => $list])
            ->call('saveAsTemplate')
            ->assertRedirect(route('lists.show', ['list' => $list->id]));

        $this->assertDatabaseHas('list_templates', [
            'user_id' => $user->id,
            'title' => 'Dinner Ideas',
            'description' => 'Weekly dinners',
        ]);

        $this->assertSame(['Pizza', 'Tacos'], ListTemplate::first()->items);
    }

    #[Test]
    public function non_owner_cannot_save_a_list_as_a_template()
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $list = DecisionList::factory()->for($owner)->create();
        $list->participants()->create(['user_id' => $participant->id]);

        Livewire::actingAs($participant)
            ->test(ShowList::class, ['list' => $list])
            ->call('saveAsTemplate')
            ->assertForbidden();

        $this->assertDatabaseCount('list_templates', 0);
    }
}
