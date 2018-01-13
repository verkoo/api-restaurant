<?php

use Carbon\Carbon;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Invoice;
use Verkoo\Common\Entities\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiInvoicesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_invoices()
    {
        $customer = create(Customer::class, [
            'name' => 'John Doe',
        ]);

        $invoice = create(Invoice::class, [
            'date'        => '20/10/2016',
            'customer_id' => $customer->id,
            'cashed_amount' => 10.27,
            'discount'      => '10,21',
        ]);

        create(Line::class, [
           'lineable_id'   => $invoice->id,
           'lineable_type' => Invoice::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->get("api/invoices");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'date'     => '20/10/2016',
            'customer_name' => 'John Doe',
            'total'    => '25,02',
            'cashed_amount' => 10.27,
            'discount'      => 10.21,
        ]);
    }

    /** @test */
    public function it_creates_a_new_invoice()
    {
        $customer = create(Customer::class);

        $response = $this->post("api/invoices", [
            'date'        => '20/10/2016',
            'serie'       => 1,
            'discount'      => '10,21',
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'discount'    => 1021,
            'customer_id' => $customer->id,
        ]);
    }

    /** @test */
    public function it_creates_an_invoice_with_lines()
    {
        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->post("api/invoices", [
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

        $this->assertDatabaseHas('invoices', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => Invoice::first()->id,
            'lineable_type' => Invoice::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);
    }

    /** @test */
    public function serie_is_required_when_creating_an_invoice()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/invoices", [
            'customer_id' => $customer->id,
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function serie_must_be_numeric_when_creating_an_invoice()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/invoices", [
            'customer_id' => $customer->id,
            'serie' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function date_is_required_when_creating_an_invoice()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/invoices", [
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_creating_an_invoice()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/invoices", [
            'date' => 'NOT VALID DATE',
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_creating_an_invoice()
    {
        $response = $this->json('POST', "api/invoices", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function all_fields_in_lines_can_be_empty()
    {
        $customer = create(Customer::class);

        $response = $this->json('POST', "api/invoices", [
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
    public function it_updates_an_invoice_with_lines()
    {
        $invoice = create(Invoice::class);
        create(Line::class, [
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->patch("api/invoices/{$invoice->id}", [
            'date'        => '20/10/2016',
            'customer_id' => $customer->id,
            'serie'       => 1,
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

        $this->assertDatabaseHas('invoices', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'cashed_amount' => 1027,
            'discount' => 1021,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => Invoice::first()->id,
            'lineable_type' => Invoice::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);

        $this->assertCount(1, $invoice->fresh()->lines);
    }

    /** @test */
    public function serie_is_required_when_updating_an_invoice()
    {
        $invoice = create(Invoice::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/invoices/{$invoice->id}", [
            'customer_id' => $customer->id,
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function serie_must_be_numeric_when_updating_an_order()
    {
        $invoice = create(Invoice::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/invoices/{$invoice->id}", [
            'customer_id' => $customer->id,
            'serie' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function cashed_amount_must_be_numeric_when_updating_an_order()
    {
        $invoice = create(Invoice::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/invoices/{$invoice->id}", [
            'customer_id' => $customer->id,
            'serie' => 1,
            'cashed_amount' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'cashed_amount');
    }

    /** @test */
    public function date_is_required_when_updating_an_invoice()
    {
        $invoice = create(Invoice::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/invoices/{$invoice->id}", [
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_updating_an_invoice()
    {
        $invoice = create(Invoice::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/invoices/{$invoice->id}", [
            'date' => 'NOT VALID DATE',
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_updating_an_invoice()
    {
        $invoice = create(Invoice::class);
        $response = $this->json('PATCH', "api/invoices/{$invoice->id}", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function it_deletes_an_invoice_with_lines()
    {
        $invoice = create(Invoice::class, [
            'date' => '20/10/2016'
        ]);

        create(Line::class, [
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->delete("api/invoices/{$invoice->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('invoices', [
            'date'        => '2016-10-20',
        ]);

        $this->assertDatabaseMissing('lines', [
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
        ]);
    }
}