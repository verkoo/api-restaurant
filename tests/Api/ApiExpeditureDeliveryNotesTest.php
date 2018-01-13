<?php

use Carbon\Carbon;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Verkoo\Common\Entities\ExpeditureDeliveryNote;

class ApiExpeditureDeliveryNotesTest extends TestCase
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
    public function it_gets_all_the_expediture_delivery_notes()
    {
        $supplier = create(Supplier::class, [
            'name' => 'John Doe',
        ]);

        $deliveryNote = create(ExpeditureDeliveryNote::class, [
            'date'        => '20/10/2016',
            'supplier_id' => $supplier->id,
            'reference'     => 'RF-123',
        ]);

        create(Line::class, [
           'lineable_id'   => $deliveryNote->id,
           'lineable_type' => ExpeditureDeliveryNote::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->get("api/expediture-delivery-notes");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'date'     => '20/10/2016',
            'supplier_name' => 'John Doe',
            'reference'     => 'RF-123',
            'total'    => '25,02',
        ]);
    }

    /** @test */
    public function it_creates_a_new_expediture_delivery_note()
    {
        $supplier = create(Supplier::class);

        $response = $this->post("api/expediture-delivery-notes", [
            'date'        => '20/10/2016',
            'supplier_id' => $supplier->id,
            'reference'     => 'RF-123',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('expediture_delivery_notes', [
            'date'        => '2016-10-20',
            'supplier_id' => $supplier->id,
            'reference'   => 'RF-123',
        ]);
    }

    /** @test */
    public function it_creates_a_new_expediture_delivery_note_with_lines()
    {
        $supplier = create(Supplier::class);
        $product = create(Product::class);

        $response = $this->post("api/expediture-delivery-notes", [
            'date'        => '20/10/2016',
            'supplier_id' => $supplier->id,
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

        $this->assertDatabaseHas('expediture_delivery_notes', [
            'date'        => '2016-10-20',
            'supplier_id' => $supplier->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => ExpeditureDeliveryNote::first()->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);
    }

    /** @test */
    public function date_is_required_when_creating_a_expediture_delivery_note()
    {
        $supplier = create(Supplier::class);
        $response = $this->json('POST', "api/expediture-delivery-notes", [
            'supplier_id' => $supplier->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_creating_a_delivery_note()
    {
        $supplier = create(Supplier::class);
        $response = $this->json('POST', "api/expediture-delivery-notes", [
            'date' => 'INVALID DATE',
            'supplier_id' => $supplier->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function supplier_is_required_when_creating_a_delivery_note()
    {
        $response = $this->json('POST', "api/expediture-delivery-notes", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'supplier_id');
    }

    /** @test */
    public function all_fields_in_lines_can_be_empty()
    {
        $supplier = create(Supplier::class);

        $response = $this->json('POST', "api/expediture-delivery-notes", [
            'date' => '20/10/2016',
            'supplier_id' => $supplier->id,
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
        $deliveryNote = create(ExpeditureDeliveryNote::class);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $supplier = create(Supplier::class);
        $product = create(Product::class);

        $response = $this->patch("api/expediture-delivery-notes/{$deliveryNote->id}", [
            'date'        => '20/10/2016',
            'supplier_id' => $supplier->id,
            'reference'     => 'RF-123',
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

        $this->assertDatabaseHas('expediture_delivery_notes', [
            'date'        => '2016-10-20',
            'supplier_id' => $supplier->id,
            'reference'     => 'RF-123',
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => ExpeditureDeliveryNote::first()->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);

        $this->assertCount(1, $deliveryNote->fresh()->lines);
    }

    /** @test */
    public function date_is_required_when_updating_a_expediture_delivery_note()
    {
        $deliveryNote = create(ExpeditureDeliveryNote::class);
        $supplier = create(Supplier::class);
        $response = $this->json('PATCH', "api/expediture-delivery-notes/{$deliveryNote->id}", [
            'supplier_id' => $supplier->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_updating_a_expediture_delivery_note()
    {
        $deliveryNote = create(ExpeditureDeliveryNote::class);
        $supplier = create(Supplier::class);
        $response = $this->json('PATCH', "api/expediture-delivery-notes/{$deliveryNote->id}", [
            'date' => 'NOT VALID DATE',
            'supplier_id' => $supplier->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function supplier_is_required_when_updating_a_expediture_delivery_note()
    {
        $deliveryNote = create(ExpeditureDeliveryNote::class);
        $response = $this->json('PATCH', "api/expediture-delivery-notes/{$deliveryNote->id}", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'supplier_id');
    }

    /** @test */
    public function it_deletes_a_delivery_note_with_lines()
    {
        $deliveryNote = create(ExpeditureDeliveryNote::class, [
            'date' => '20/10/2016'
        ]);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->delete("api/expediture-delivery-notes/{$deliveryNote->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('expediture_delivery_notes', [
            'date'        => '2016-10-20',
        ]);

        $this->assertDatabaseMissing('lines', [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
        ]);
    }
}