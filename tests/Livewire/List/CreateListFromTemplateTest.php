<?php

namespace Tests\Livewire\List;

use App\Livewire\List\CreateList;
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
