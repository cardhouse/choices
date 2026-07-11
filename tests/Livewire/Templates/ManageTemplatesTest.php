<?php

namespace Tests\Livewire\Templates;

use App\Livewire\Templates\ManageTemplates;
use App\Models\ListTemplate;
use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManageTemplatesTest extends TestCase
{
    #[Test]
    public function guests_are_redirected_to_login()
    {
        $this->get(route('templates.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function it_renders_the_users_templates()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->for($user)->create(['title' => 'Weeknight Dinners']);
        $otherTemplate = ListTemplate::factory()->create(['title' => 'Someone Elses Template']);

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->assertStatus(200)
            ->assertSee('My Templates')
            ->assertSee('Weeknight Dinners')
            ->assertDontSee('Someone Elses Template');
    }

    #[Test]
    public function user_can_create_a_template()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->call('createTemplate')
            ->assertSet('showForm', true)
            ->set('title', 'Dinner Ideas')
            ->set('description', 'Our usual dinner options')
            ->set('items', ['Pizza', 'Tacos', 'Sushi'])
            ->call('saveTemplate')
            ->assertSet('showForm', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('list_templates', [
            'user_id' => $user->id,
            'title' => 'Dinner Ideas',
            'description' => 'Our usual dinner options',
        ]);

        $this->assertSame(['Pizza', 'Tacos', 'Sushi'], ListTemplate::first()->items);
    }

    #[Test]
    public function template_requires_a_title_and_two_items()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->call('createTemplate')
            ->set('title', '')
            ->set('items', ['Only One', ''])
            ->call('saveTemplate')
            ->assertHasErrors(['title', 'items']);

        $this->assertDatabaseCount('list_templates', 0);
    }

    #[Test]
    public function user_can_update_their_template()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->for($user)->create([
            'title' => 'Old Title',
            'items' => ['One', 'Two'],
        ]);

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->call('editTemplate', $template->id)
            ->assertSet('title', 'Old Title')
            ->set('title', 'New Title')
            ->set('items', ['One', 'Two', 'Three'])
            ->call('saveTemplate')
            ->assertHasNoErrors();

        $template->refresh();

        $this->assertSame('New Title', $template->title);
        $this->assertSame(['One', 'Two', 'Three'], $template->items);
    }

    #[Test]
    public function user_cannot_edit_or_delete_another_users_template()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->create();

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->call('editTemplate', $template->id)
            ->assertForbidden();

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->call('deleteTemplate', $template->id)
            ->assertForbidden();

        $this->assertDatabaseHas('list_templates', ['id' => $template->id]);
    }

    #[Test]
    public function user_can_delete_their_template()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->call('deleteTemplate', $template->id);

        $this->assertDatabaseMissing('list_templates', ['id' => $template->id]);
    }

    #[Test]
    public function using_a_template_redirects_to_create_list_with_the_template()
    {
        $user = User::factory()->create();
        $template = ListTemplate::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(ManageTemplates::class)
            ->call('useTemplate', $template->id)
            ->assertRedirect(route('lists.create', ['template' => $template->id]));
    }
}
