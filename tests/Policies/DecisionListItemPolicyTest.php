<?php

namespace Tests\Policies;

use App\Models\DecisionListItem;
use App\Models\User;
use App\Policies\DecisionListItemPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecisionListItemPolicyTest extends TestCase
{
    use RefreshDatabase;

    private DecisionListItemPolicy $policy;

    private User $user;

    private DecisionListItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new DecisionListItemPolicy;
        $this->user = User::factory()->create();
        $this->item = DecisionListItem::factory()->create();
    }

    /**
     * Test that a user can view an item if they can view the parent list.
     */
    public function test_user_can_view_item_if_can_view_list(): void
    {
        $this->item->list->user_id = $this->user->id;
        $this->item->list->save();

        $this->assertTrue($this->policy->view($this->user, $this->item));
    }

    /**
     * Test that a user cannot view an item if they cannot view the parent list.
     */
    public function test_user_cannot_view_item_if_cannot_view_list(): void
    {
        $this->item->list->is_anonymous = false;
        $this->item->list->claimed_at = null;
        $this->item->list->user_id = User::factory()->create()->id;
        $this->item->list->save();

        $this->assertFalse($this->policy->view($this->user, $this->item));
    }

    /**
     * Test that a user can create an item if they can update the parent list.
     */
    public function test_user_can_create_item_if_can_update_list(): void
    {
        $this->item->list->user_id = $this->user->id;
        $this->item->list->save();

        $this->assertTrue($this->policy->create($this->user, $this->item));
    }

    /**
     * Test that a user cannot create an item if they cannot update the parent list.
     */
    public function test_user_cannot_create_item_if_cannot_update_list(): void
    {
        $this->assertFalse($this->policy->create($this->user, $this->item));
    }

    /**
     * Test that a user can update an item if they can update the parent list.
     */
    public function test_user_can_update_item_if_can_update_list(): void
    {
        $this->item->list->user_id = $this->user->id;
        $this->item->list->save();

        $this->assertTrue($this->policy->update($this->user, $this->item));
    }

    /**
     * Test that a user cannot update an item if they cannot update the parent list.
     */
    public function test_user_cannot_update_item_if_cannot_update_list(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->item));
    }

    /**
     * Test that a user can delete an item if they can update the parent list.
     */
    public function test_user_can_delete_item_if_can_update_list(): void
    {
        $this->item->list->user_id = $this->user->id;
        $this->item->list->save();

        $this->assertTrue($this->policy->delete($this->user, $this->item));
    }

    /**
     * Test that a user cannot delete an item if they cannot update the parent list.
     */
    public function test_user_cannot_delete_item_if_cannot_update_list(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->item));
    }
}
