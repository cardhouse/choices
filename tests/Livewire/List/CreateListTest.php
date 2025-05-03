<?php

namespace Tests\Livewire\List;

use App\Livewire\List\CreateList;
use App\Models\DecisionList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CreateListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_successfully()
    {
        Livewire::test(CreateList::class)
            ->assertStatus(200)
            ->assertSee('Create a Decision List')
            ->assertSee('Title')
            ->assertSee('Description')
            ->assertSee('Items')
            ->assertSee('Create List');
    }

    #[Test]
    public function authenticated_user_can_create_list()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Test List')
            ->set('description', 'Test Description')
            ->set('items', ['Item 1', 'Item 2', 'Item 3'])
            ->call('createList')
            ->assertRedirect(route('lists.show', ['list' => DecisionList::first()->id]));

        $this->assertDatabaseHas('decision_lists', [
            'title' => 'Test List',
            'description' => 'Test Description',
            'user_id' => $user->id,
            'is_anonymous' => false,
        ]);

        $list = DecisionList::first();
        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 1',
        ]);

        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 2',
        ]);

        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 3',
        ]);
    }

    #[Test]
    public function unauthenticated_user_can_create_anonymous_list()
    {
        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('description', 'Test Description')
            ->set('items', ['Item 1', 'Item 2', 'Item 3'])
            ->call('createList')
            ->assertRedirect(route('lists.show', ['list' => DecisionList::first()->id]));

        $this->assertDatabaseHas('decision_lists', [
            'title' => 'Test List',
            'description' => 'Test Description',
            'user_id' => null,
            'is_anonymous' => true,
        ]);

        $list = DecisionList::first();
        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 1',
        ]);

        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 2',
        ]);

        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 3',
        ]);
    }

    #[Test]
    public function authenticated_user_can_create_anonymous_list()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Test List')
            ->set('description', 'Test Description')
            ->set('items', ['Item 1', 'Item 2', 'Item 3'])
            ->set('isAnonymous', true)
            ->call('createList')
            ->assertRedirect(route('lists.show', ['list' => DecisionList::first()->id]));

        $this->assertDatabaseHas('decision_lists', [
            'title' => 'Test List',
            'description' => 'Test Description',
            'user_id' => null,
            'is_anonymous' => true,
        ]);

        $list = DecisionList::first();
        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 1',
        ]);

        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 2',
        ]);

        $this->assertDatabaseHas('decision_list_items', [
            'list_id' => $list->id,
            'label' => 'Item 3',
        ]);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        Livewire::test(CreateList::class)
            ->set('title', '')
            ->set('items', [''])
            ->call('createList')
            ->assertHasErrors(['title', 'items', 'items.0']);
    }

    #[Test]
    public function it_validates_item_count()
    {
        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', ['Item 1'])
            ->call('createList')
            ->assertHasErrors(['items']);

        $items = [];
        for ($i = 0; $i < 101; $i++) {
            $items[] = "Item {$i}";
        }

        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', $items)
            ->call('createList')
            ->assertHasErrors(['items']);
    }

    #[Test]
    public function it_validates_item_length()
    {
        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', [''])
            ->call('createList')
            ->assertHasErrors(['items.0']);

        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', [str_repeat('a', 256)])
            ->call('createList')
            ->assertHasErrors(['items.0']);
    }

    #[Test]
    public function it_can_add_and_remove_items()
    {
        Livewire::test(CreateList::class)
            ->call('addItem')
            ->assertSet('items', ['', '', ''])
            ->call('removeItem', 0)
            ->assertSet('items', ['', '']);
    }
}
