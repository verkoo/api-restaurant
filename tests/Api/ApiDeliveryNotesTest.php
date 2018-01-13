<?php

use Carbon\Carbon;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DeliveryNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiDeliveryNotesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_delivery_notes()
    {
        $this->disableExceptionHandling();
        $customer = create(Customer::class, [
            'name'      => 'John Doe',
        ]);

        $deliveryNote = create(DeliveryNote::class, [
            'date'          => '20/10/2016',
            'customer_id'   => $customer->id,
            'cashed_amount' => 10.27,
            'discount'      => '10,21',
        ]);

        create(Line::class, [
           'lineable_id'   => $deliveryNote->id,
           'lineable_type' => DeliveryNote::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->get("api/delivery-notes");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'date'          => '20/10/2016',
            'customer_name' => 'John Doe',
            'total'         => '25,02',
            'cashed_amount' => 10.27,
            'discount'      => 10.21,
        ]);
    }

    /** @test */
    public function it_creates_a_new_delivery_note()
    {
        $customer = create(Customer::class);

        $response = $this->post("api/delivery-notes", [
            'date'          => '20/10/2016',
            'serie'         => 1,
            'customer_id'   => $customer->id,
            'discount'      => '10,21',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('delivery_notes', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'customer_id' => $customer->id,
            'discount'    => 1021,
        ]);
    }

    /** @test */
    public function it_creates_a_new_delivery_note_with_lines()
    {
        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->post("api/delivery-notes", [
            'date'        => '20/10/2016',
            'customer_id' => $customer->id,
            'serie'       => 1,
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

        $this->assertDatabaseHas('delivery_notes', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => DeliveryNote::first()->id,
            'lineable_type' => DeliveryNote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);
    }

    /** @test */
    public function serie_is_required_when_creating_a_delivery_note()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/delivery-notes", [
            'customer_id' => $customer->id,
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function serie_must_be_numeric_when_creating_a_delivery_note()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/delivery-notes", [
            'customer_id' => $customer->id,
            'serie' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function date_is_required_when_creating_a_delivery_note()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/delivery-notes", [
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_creating_a_delivery_note()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/delivery-notes", [
            'date' => 'NOT VALID DATE',
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_creating_a_delivery_note()
    {
        $response = $this->json('POST', "api/delivery-notes", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function all_fields_in_lines_can_be_empty()
    {
        $customer = create(Customer::class);

        $response = $this->json('POST', "api/delivery-notes", [
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
        $deliveryNote = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'price'        => '12,51',
            'quantity'     => 2,
            'discount'      => '10',
        ]);

        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->patch("api/delivery-notes/{$deliveryNote->id}", [
            'date'          => '20/10/2016',
            'serie'         => 1,
            'customer_id'   => $customer->id,
            'discount'      => '10,21',
            'cashed_amount' => 10.27,
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

        $this->assertDatabaseHas('delivery_notes', [
            'date'          => '2016-10-20',
            'serie'         => 1,
            'cashed_amount' => 1027,
            'discount'      => 1021,
            'customer_id'   => $customer->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => DeliveryNote::first()->id,
            'lineable_type' => DeliveryNote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);

        $this->assertCount(1, $deliveryNote->fresh()->lines);
    }

    /** @test */
    public function serie_is_required_when_updating_an_order()
    {
        $deliveryNote = create(DeliveryNote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/delivery-notes/{$deliveryNote->id}", [
            'customer_id' => $customer->id,
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function serie_must_be_numeric_when_updating_an_order()
    {
        $deliveryNote = create(DeliveryNote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/delivery-notes/{$deliveryNote->id}", [
            'customer_id' => $customer->id,
            'serie' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function cashed_amount_must_be_numeric_when_updating_an_order()
    {
        $deliveryNote = create(DeliveryNote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/delivery-notes/{$deliveryNote->id}", [
            'customer_id' => $customer->id,
            'serie' => 1,
            'cashed_amount' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'cashed_amount');
    }

    /** @test */
    public function date_is_required_when_updating_a_delivery_note()
    {
        $deliveryNote = create(DeliveryNote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/delivery-notes/{$deliveryNote->id}", [
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_updating_a_delivery_note()
    {
        $deliveryNote = create(DeliveryNote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/delivery-notes/{$deliveryNote->id}", [
            'date' => 'NOT VALID DATE',
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_updating_a_delivery_note()
    {
        $deliveryNote = create(DeliveryNote::class);
        $response = $this->json('PATCH', "api/delivery-notes/{$deliveryNote->id}", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }


    /** @test */
    public function it_deletes_a_delivery_note_with_lines()
    {
        $deliveryNote = create(DeliveryNote::class, [
            'date' => '20/10/2016'
        ]);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->delete("api/delivery-notes/{$deliveryNote->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('delivery_notes', [
            'date'        => '2016-10-20',
        ]);

        $this->assertDatabaseMissing('lines', [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
        ]);
    }
}