<?php

namespace Tests\Http\Requests;

use App\Http\Requests\VoteRequest;
use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Matchup;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class VoteRequestTest extends TestCase
{
    private VoteRequest $request;

    private Matchup $matchup;

    private DecisionListItem $itemA;

    private DecisionListItem $itemB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new VoteRequest;

        // Create a list with two items and a matchup
        $list = DecisionList::factory()->create();
        $this->itemA = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $this->itemB = DecisionListItem::factory()->create(['list_id' => $list->id]);
        $this->matchup = Matchup::factory()->create([
            'list_id' => $list->id,
            'item_a_id' => $this->itemA->id,
            'item_b_id' => $this->itemB->id,
        ]);
    }

    /**
     * Test that the request is authorized.
     */
    public function test_request_is_authorized(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    /**
     * Test that valid data passes validation.
     */
    public function test_valid_data_passes_validation(): void
    {
        $data = [
            'matchup_id' => $this->matchup->id,
            'chosen_item_id' => $this->itemA->id,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that matchup_id is required.
     */
    public function test_matchup_id_is_required(): void
    {
        $data = [
            'chosen_item_id' => $this->itemA->id,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('matchup_id'));
    }

    /**
     * Test that matchup_id must exist.
     */
    public function test_matchup_id_must_exist(): void
    {
        $data = [
            'matchup_id' => 999,
            'chosen_item_id' => $this->itemA->id,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('matchup_id'));
    }

    /**
     * Test that chosen_item_id is required.
     */
    public function test_chosen_item_id_is_required(): void
    {
        $data = [
            'matchup_id' => $this->matchup->id,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('chosen_item_id'));
    }

    /**
     * Test that chosen_item_id must exist.
     */
    public function test_chosen_item_id_must_exist(): void
    {
        $data = [
            'matchup_id' => $this->matchup->id,
            'chosen_item_id' => PHP_INT_MAX,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('chosen_item_id'));
    }

    /**
     * Test that chosen_item_id must be one of the items in the matchup.
     */
    public function test_chosen_item_id_must_be_in_matchup(): void
    {
        // Create a new item that doesn't belong to the matchup
        $otherItem = DecisionListItem::factory()->create(['list_id' => $this->matchup->list->id]);

        $data = [
            'matchup_id' => $this->matchup->id,
            'chosen_item_id' => $otherItem->id,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('chosen_item_id'));
    }
}
