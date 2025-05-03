<?php

namespace Tests\Http\Requests;

use App\Http\Requests\StoreListRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreListRequestTest extends TestCase
{
    private StoreListRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new StoreListRequest;
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
            'title' => 'Test List',
            'description' => 'A test list description',
            'items' => ['Item 1', 'Item 2'],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that title is required.
     */
    public function test_title_is_required(): void
    {
        $data = [
            'description' => 'A test list description',
            'items' => ['Item 1', 'Item 2'],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
    }

    /**
     * Test that title has a maximum length of 255 characters.
     */
    public function test_title_has_maximum_length(): void
    {
        $data = [
            'title' => str_repeat('a', 256),
            'description' => 'A test list description',
            'items' => ['Item 1', 'Item 2'],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
    }

    /**
     * Test that description has a maximum length of 1000 characters.
     */
    public function test_description_has_maximum_length(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => str_repeat('a', 1001),
            'items' => ['Item 1', 'Item 2'],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('description'));
    }

    /**
     * Test that items array is required.
     */
    public function test_items_array_is_required(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => 'A test list description',
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('items'));
    }

    /**
     * Test that items array has a minimum of 2 items.
     */
    public function test_items_array_has_minimum_items(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => 'A test list description',
            'items' => ['Item 1'],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('items'));
    }

    /**
     * Test that items array has a maximum of 100 items.
     */
    public function test_items_array_has_maximum_items(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => 'A test list description',
            'items' => array_fill(0, 101, 'Item'),
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('items'));
    }

    /**
     * Test that each item is required.
     */
    public function test_each_item_is_required(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => 'A test list description',
            'items' => ['Item 1', ''],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('items.1'));
    }

    /**
     * Test that each item has a minimum length of 1 character.
     */
    public function test_each_item_has_minimum_length(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => 'A test list description',
            'items' => ['Item 1', ''],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('items.1'));
    }

    /**
     * Test that each item has a maximum length of 255 characters.
     */
    public function test_each_item_has_maximum_length(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => 'A test list description',
            'items' => ['Item 1', str_repeat('a', 256)],
            'is_anonymous' => false,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('items.1'));
    }

    /**
     * Test that is_anonymous is optional and boolean.
     */
    public function test_is_anonymous_is_optional_and_boolean(): void
    {
        $data = [
            'title' => 'Test List',
            'description' => 'A test list description',
            'items' => ['Item 1', 'Item 2'],
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }
}
