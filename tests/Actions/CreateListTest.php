<?php

namespace Tests\Actions;

use App\Actions\Lists\CreateList;
use App\Jobs\DeleteUnclaimedList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateListTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_owned_list_with_items_and_matchups(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $list = app(CreateList::class)->handle([
            'title' => 'Lunch Spots',
            'description' => 'Where to eat',
            'items' => ['Tacos', 'Pizza', 'Sushi'],
        ], $user);

        $this->assertEquals($user->id, $list->user_id);
        $this->assertFalse($list->is_anonymous);
        $this->assertEquals(3, $list->items()->count());
        $this->assertEquals(3, $list->matchups()->count());

        Queue::assertNotPushed(DeleteUnclaimedList::class);
    }

    public function test_creates_anonymous_list_and_schedules_deletion(): void
    {
        Queue::fake();

        $list = app(CreateList::class)->handle([
            'title' => 'Quick Pick',
            'items' => ['A', 'B'],
        ], null);

        $this->assertNull($list->user_id);
        $this->assertTrue($list->is_anonymous);

        Queue::assertPushedOn('deletions', DeleteUnclaimedList::class);
        Queue::assertPushed(fn (DeleteUnclaimedList $job) => $job->list->id === $list->id);
    }
}
