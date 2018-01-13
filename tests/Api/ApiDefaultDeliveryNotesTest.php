<?php

use Carbon\Carbon;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DefaultDeliveryNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiDefaultDeliveryNotesTest extends TestCase
{
    use DatabaseTransactions;

    protected $transformer;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_default_delivery_notes()
    {
        $this->disableExceptionHandling();
        $customer = create(Customer::class, [
            'name' => 'John Doe',
        ]);

        $deliveryNote = create(DefaultDeliveryNote::class, [
            'customer_id' => $customer->id,
        ]);

        create(Line::class, [
           'lineable_id'   => $deliveryNote->id,
           'lineable_type' => DefaultDeliveryNote::class,
        ]);

        $response = $this->get("api/default-delivery-notes");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'customer_name' => 'John Doe',
        ]);
    }

    /** @test */
    public function it_creates_a_default_delivery_note()
    {
        $customer = create(Customer::class);

        $response = $this->post("api/default-delivery-notes", [
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('default_delivery_notes', [
            'customer_id' => $customer->id,
        ]);
    }

    /** @test */
    public function only_a_default_delivery_note_is_allowed_for_each_customer()
    {
        $customer = create(Customer::class);
        create(DefaultDeliveryNote::class, [
            'customer_id' => $customer->id
        ]);

        $response = $this->post("api/default-delivery-notes", [
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(422);

        $this->assertCount(1,DefaultDeliveryNote::all());
    }

    /** @test */
    public function it_creates_a_new_default_delivery_note_with_lines()
    {
        $this->disableExceptionHandling();
        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->post("api/default-delivery-notes", [
            'customer_id' => $customer->id,
            'lines'       => [
                0 => [
                    'product_id'   => $product->id,
                    'product_name' => 'CUSTOM PRODUCT NAME',
                    'price'        => '12,20',
                    'quantity'     => 2,
                ]
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('default_delivery_notes', [
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => DefaultDeliveryNote::first()->id,
            'lineable_type' => DefaultDeliveryNote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);
    }

    /** @test */
    public function customer_is_required_when_creating_a_default_delivery_note()
    {
        $response = $this->json('POST', "api/default-delivery-notes", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function all_fields_in_lines_can_be_empty_creating_a_default_delivery_note()
    {
        $this->disableExceptionHandling();
        $customer = create(Customer::class);

        $response = $this->json('POST', "api/default-delivery-notes", [
            'date' => '20/10/2016',
            'customer_id' => $customer->id,
            'serie'       => 1,
            'lines' => [
                0 => [
                    'product_id'   => '',
                    'product_name' => '',
                    'price'        => '',
                    'quantity'     => '',
                ]
            ]
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_updates_a_delivery_note_with_lines()
    {
        $deliveryNote = create(DefaultDeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DefaultDeliveryNote::class,
        ]);

        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->patch("api/default-delivery-notes/{$deliveryNote->id}", [
            'customer_id'   => $customer->id,
            'lines'       => [
                0 => [
                    'product_id'   => $product->id,
                    'product_name' => 'CUSTOM PRODUCT NAME',
                    'price'        => '12,20',
                    'quantity'     => 2,
                ]
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('default_delivery_notes', [
            'customer_id'   => $customer->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => DefaultDeliveryNote::first()->id,
            'lineable_type' => DefaultDeliveryNote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);

        $this->assertCount(1, $deliveryNote->fresh()->lines);
    }

    /** @test */
    public function customer_is_required_when_updating_a_default_delivery_note()
    {
        $deliveryNote = create(DefaultDeliveryNote::class);
        $response = $this->json('PATCH', "api/default-delivery-notes/{$deliveryNote->id}", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function it_deletes_a_default_delivery_note_with_lines()
    {
        $customer = create(Customer::class);
        $deliveryNote = create(DefaultDeliveryNote::class, [
            'customer_id' => $customer->id
        ]);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DefaultDeliveryNote::class,
        ]);

        $this->assertDatabaseHas('default_delivery_notes', [
            'customer_id' => $customer->id
        ]);

        $response = $this->delete("api/default-delivery-notes/{$deliveryNote->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('default_delivery_notes', [
            'customer_id' => $customer->id
        ]);

        $this->assertDatabaseMissing('lines', [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DefaultDeliveryNote::class,
        ]);
    }
}