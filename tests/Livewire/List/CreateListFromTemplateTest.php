<?php

namespace Tests\Livewire\List;

use App\Livewire\List\CreateList;
use App\Models\DecisionList;
use App\Models\ListTemplate;
use App\Models\User;
use App\Support\ExampleLists;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateListFromTemplateTest extends TestCase
{
    #[Test]
    public function create_page_prefills_from_a_template_query_param()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->for($user)->create([
            'title' => 'Dinner Ideas',
            'description' => 'Our usual options',
            'items' => ['Pizza', 'Tacos', 'Sushi'],
        ]);

        $this->actingAs($user)
            ->get(route('lists.create', ['template' => $template->id]))
            ->assertStatus(200)
            ->assertSee('Dinner Ideas')
            ->assertSee('Our usual options');
    }

    #[Test]
    public function template_query_param_is_ignored_for_other_users_templates()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->create(['title' => 'Not Yours']);

        $this->actingAs($user)
            ->get(route('lists.create', ['template' => $template->id]))
            ->assertStatus(200)
            ->assertDontSee('Not Yours');
    }

    #[Test]
    public function arriving_from_a_template_offers_use_as_is_or_make_updates()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->for($user)->create([
            'title' => 'Dinner Ideas',
            'items' => ['Pizza', 'Tacos', 'Sushi'],
        ]);

        $this->actingAs($user)
            ->get(route('lists.create', ['template' => $template->id]))
            ->assertStatus(200)
            ->assertSee('Use as is')
            ->assertSee('Make Updates')
            ->assertSee('Pizza')
            // the editable item form stays hidden until "Make Updates"
            ->assertDontSee('Enter an item');
    }

    #[Test]
    public function use_as_is_creates_the_list_and_starts_the_voting_flow()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Dinner Ideas')
            ->set('description', 'Our usual options')
            ->set('items', ['Pizza', 'Tacos', 'Sushi'])
            ->call('useAsIs')
            ->assertRedirect(route('lists.vote', ['list' => DecisionList::first()->id]));

        $this->assertDatabaseHas('decision_lists', [
            'user_id' => $user->id,
            'title' => 'Dinner Ideas',
        ]);

        // Matchups are generated at creation, so the list is ready to vote on.
        $this->assertGreaterThan(0, DecisionList::first()->matchups()->count());
    }

    #[Test]
    public function make_updates_reveals_the_editable_item_form()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('fromTemplate', true)
            ->set('editing', false)
            ->set('title', 'Dinner Ideas')
            ->set('items', ['Pizza', 'Tacos'])
            ->assertDontSee('Enter an item')
            ->call('makeUpdates')
            ->assertSet('editing', true)
            ->assertSee('Enter an item')
            ->assertSee('Add Another Item');
    }

    #[Test]
    public function items_can_be_added_and_removed_after_choosing_make_updates()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('fromTemplate', true)
            ->set('items', ['Pizza', 'Tacos'])
            ->call('makeUpdates')
            ->call('addItem')
            ->assertCount('items', 3)
            ->set('items.2', 'Sushi')
            ->call('removeItem', 0)
            ->assertCount('items', 2)
            ->assertSet('items', ['Tacos', 'Sushi']);
    }

    #[Test]
    public function user_can_apply_a_saved_template_from_the_picker()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->for($user)->create([
            'title' => 'Dinner Ideas',
            'description' => 'Our usual options',
            'items' => ['Pizza', 'Tacos', 'Sushi'],
        ]);

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->call('applyTemplate', $template->id)
            ->assertSet('title', 'Dinner Ideas')
            ->assertSet('description', 'Our usual options')
            ->assertSet('items', ['Pizza', 'Tacos', 'Sushi']);
    }

    #[Test]
    public function user_cannot_apply_another_users_template()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->call('applyTemplate', $template->id)
            ->assertForbidden();
    }

    #[Test]
    public function anyone_can_apply_a_prebuilt_example()
    {
        $example = ExampleLists::all()[0];

        Livewire::test(CreateList::class)
            ->call('applyExample', 0)
            ->assertSet('title', $example['title'])
            ->assertSet('description', $example['description'])
            ->assertSet('items', $example['items']);
    }

    #[Test]
    public function creating_a_list_can_also_save_it_as_a_template()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Movie Night')
            ->set('description', 'What to watch')
            ->set('items', ['Alien', 'Heat', 'Up'])
            ->set('saveAsTemplate', true)
            ->call('createList');

        $this->assertDatabaseHas('list_templates', [
            'user_id' => $user->id,
            'title' => 'Movie Night',
            'description' => 'What to watch',
        ]);

        $this->assertSame(['Alien', 'Heat', 'Up'], ListTemplate::first()->items);

        $this->assertDatabaseHas('decision_lists', [
            'user_id' => $user->id,
            'title' => 'Movie Night',
        ]);
    }

    #[Test]
    public function creating_a_list_without_the_option_does_not_save_a_template()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Movie Night')
            ->set('items', ['Alien', 'Heat'])
            ->call('createList');

        $this->assertDatabaseCount('list_templates', 0);
    }
}
