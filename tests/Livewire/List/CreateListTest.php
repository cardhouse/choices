<?php

namespace Tests\Livewire\List;

use App\Livewire\List\CreateList;
use App\Models\DecisionList;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the component can be rendered.
     */
    public function test_component_can_be_rendered(): void
    {
        $component = Livewire::test(CreateList::class);

        $component->assertStatus(200);
    }

    /**
     * Test that an authenticated user can create a list.
     */
    public function test_authenticated_user_can_create_list(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Test List')
            ->set('description', 'Test Description')
            ->set('items', ['Item 1', 'Item 2'])
            ->call('createList');

        $list = DecisionList::first();
        $component->assertRedirect(route('lists.show', ['list' => $list->id]));

        $this->assertDatabaseHas('decision_lists', [
            'title' => 'Test List',
            'description' => 'Test Description',
            'user_id' => $user->id,
            'is_anonymous' => false,
        ]);

        $this->assertDatabaseHas('items', [
            'label' => 'Item 1',
        ]);

        $this->assertDatabaseHas('items', [
            'label' => 'Item 2',
        ]);
    }

    /**
     * Test that an unauthenticated user can create an anonymous list.
     */
    public function test_unauthenticated_user_can_create_anonymous_list(): void
    {
        $component = Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('description', 'Test Description')
            ->call('nextStep')
            ->set('items', ['Item 1', 'Item 2'])
            ->call('createList');

        $this->assertDatabaseHas('decision_lists', [
            'title' => 'Test List',
            'description' => 'Test Description',
            'user_id' => null,
            'is_anonymous' => true,
        ]);

        $list = DecisionList::with('items')->first();
        $this->assertNotNull($list);
        $component->assertRedirect(route('lists.show', ['list' => $list->id]));

        $this->assertDatabaseHas('items', [
            'list_id' => $list->id,
            'label' => 'Item 1',
        ]);

        $this->assertDatabaseHas('items', [
            'list_id' => $list->id,
            'label' => 'Item 2',
        ]);

        $this->assertDatabaseCount('matchups', ($list->items->count() * ($list->items->count() - 1)) / 2);
    }

    /**
     * Test that an authenticated user can create an anonymous list.
     */
    public function test_authenticated_user_can_create_anonymous_list(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Test List')
            ->set('description', 'Test Description')
            ->call('nextStep')
            ->set('items', ['Item 1', 'Item 2'])
            ->set('isAnonymous', true)
            ->call('createList');

        $this->assertDatabaseHas('decision_lists', [
            'title' => 'Test List',
            'description' => 'Test Description',
            'user_id' => null,
            'is_anonymous' => true,
        ]);

        $list = DecisionList::with('items')->first();
        $this->assertNotNull($list);
        $component->assertRedirect(route('lists.show', ['list' => $list->id]));

        $this->assertDatabaseHas('items', [
            'list_id' => $list->id,
            'label' => 'Item 1',
        ]);

        $this->assertDatabaseHas('items', [
            'list_id' => $list->id,
            'label' => 'Item 2',
        ]);

        $this->assertDatabaseCount('matchups', ($list->items->count() * ($list->items->count() - 1)) / 2);
    }

    /**
     * Test that the component validates required fields.
     */
    public function test_component_validates_required_fields(): void
    {
        Livewire::test(CreateList::class)
            ->set('title', '')
            ->set('items', [''])
            ->call('createList')
            ->assertHasErrors(['title', 'items', 'items.0']);
    }

    /**
     * Test that the component validates item count.
     */
    public function test_component_validates_item_count(): void
    {
        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', ['Item 1'])
            ->call('createList')
            ->assertHasErrors(['items']);

        $items = array_fill(0, 101, 'Item');
        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', $items)
            ->call('createList')
            ->assertHasErrors(['items']);
    }

    /**
     * Test that the component validates item length.
     */
    public function test_component_validates_item_length(): void
    {
        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', ['', str_repeat('a', 256)])
            ->call('createList')
            ->assertHasErrors(['items.0', 'items.1']);
    }

    /**
     * Test that the component can add and remove items.
     */
    public function test_component_can_add_and_remove_items(): void
    {
        $component = Livewire::test(CreateList::class);

        $component->call('addItem')
            ->assertSet('items', ['', '', '']);

        $component->call('removeItem', 1)
            ->assertSet('items', ['', '']);
    }

    /**
     * Test that the component cannot remove items below minimum count.
     */
    public function test_component_cannot_remove_items_below_minimum(): void
    {
        $component = Livewire::test(CreateList::class);

        $component->call('removeItem', 0)
            ->assertSet('items', ['', '']);

        $component->call('removeItem', 1)
            ->assertSet('items', ['', '']);
    }

    /**
     * Test that the component cannot add items above maximum count.
     */
    public function test_component_cannot_add_items_above_maximum(): void
    {
        $component = Livewire::test(CreateList::class);

        // Add 98 more items to reach the maximum
        for ($i = 0; $i < 98; $i++) {
            $component->call('addItem');
        }

        $items = array_fill(0, 100, '');
        $component->call('addItem')
            ->assertSet('items', $items);
    }

    /**
     * Test that the component can navigate between steps.
     */
    public function test_component_can_navigate_between_steps(): void
    {
        $component = Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('description', 'Test Description');

        $component->assertSet('currentStep', 1)
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->call('previousStep')
            ->assertSet('currentStep', 1);
    }

    /**
     * Test that the component validates step 1 before proceeding.
     */
    public function test_component_validates_step_1_before_proceeding(): void
    {
        Livewire::test(CreateList::class)
            ->set('title', '')
            ->call('nextStep')
            ->assertHasErrors(['title'])
            ->assertSet('currentStep', 1);
    }
} 