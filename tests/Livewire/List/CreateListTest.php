<?php

namespace Tests\Livewire\List;

use App\Jobs\DeleteUnclaimedList;
use App\Livewire\List\CreateList;
use App\Models\DecisionList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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

        foreach (['Item 1', 'Item 2', 'Item 3'] as $label) {
            $this->assertDatabaseHas('decision_list_items', [
                'list_id' => $list->id,
                'label' => $label,
            ]);
        }

        // Round-robin matchups are generated at creation
        $this->assertEquals(3, $list->matchups()->count());
    }

    #[Test]
    public function unauthenticated_user_creates_anonymous_list_scheduled_for_deletion()
    {
        Queue::fake();

        Livewire::test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', ['Item 1', 'Item 2'])
            ->call('createList')
            ->assertRedirect(route('lists.show', ['list' => DecisionList::first()->id]));

        $this->assertDatabaseHas('decision_lists', [
            'title' => 'Test List',
            'user_id' => null,
            'is_anonymous' => true,
        ]);

        Queue::assertPushed(DeleteUnclaimedList::class);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        Livewire::test(CreateList::class)
            ->set('title', '')
            ->set('items', ['', ''])
            ->call('createList')
            ->assertHasErrors(['title', 'items']);
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
            ->set('items', [str_repeat('a', 256), 'ok'])
            ->call('createList')
            ->assertHasErrors(['items.0']);
    }

    #[Test]
    public function blank_items_are_ignored()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateList::class)
            ->set('title', 'Test List')
            ->set('items', ['Item 1', '', 'Item 2', '  '])
            ->call('createList');

        $this->assertEquals(2, DecisionList::first()->items()->count());
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
