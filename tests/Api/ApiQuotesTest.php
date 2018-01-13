<?php

use Carbon\Carbon;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\Quote;
use Tests\TestCase;
use App\Entities\Line;
use Verkoo\Common\Entities\User;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiQuotesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_quotes()
    {
        $customer = create(Customer::class, [
            'name' => 'John Doe',
        ]);

        $quote = create(Quote::class, [
            'date'          => '20/10/2016',
            'customer_id'   => $customer->id,
            'cashed_amount' => 10.27,
            'discount'      => '10,21',
        ]);

        create(Line::class, [
           'lineable_id'   => $quote->id,
           'lineable_type' => Quote::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->get("api/quotes");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'date'          => '20/10/2016',
            'customer_name' => 'John Doe',
            'discount'      => 10.21,
            'total'         => '25,02',
            'cashed_amount' => 10.27,
        ]);
    }

    /** @test */
    public function it_creates_a_new_quote()
    {
        $customer = create(Customer::class);

        $response = $this->post("api/quotes", [
            'date'        => '20/10/2016',
            'discount'    => '10,28',
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('quotes', [
            'date'        => '2016-10-20',
            'discount'    => 1028,
            'customer_id' => $customer->id,
        ]);
    }

    /** @test */
    public function it_creates_a_quote_with_lines()
    {
        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->post("api/quotes", [
            'date'        => '20/10/2016',
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

        $this->assertDatabaseHas('quotes', [
            'date'        => '2016-10-20',
            'discount'     => 0,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => Quote::first()->id,
            'lineable_type' => Quote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);
    }

    /** @test */
    public function date_is_required_when_creating_a_quote()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/quotes", [
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_creating_a_quote()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/quotes", [
            'date' => 'NOT VALID DATE',
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_creating_a_quote()
    {
        $response = $this->json('POST', "api/quotes", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function all_fields_in_lines_can_be_empty()
    {
        $customer = create(Customer::class);

        $response = $this->json('POST', "api/quotes", [
            'date' => '20/10/2016',
            'customer_id' => $customer->id,
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
    public function it_updates_a_quote_with_lines()
    {
        $quote = create(Quote::class);
        create(Line::class, [
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
            'price'        => '12,51',
            'discount'     => '0',
            'quantity'     => 2,
        ]);

        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->patch("api/quotes/{$quote->id}", [
            'date'        => '20/10/2016',
            'discount'     => '12,10',
            'customer_id' => $customer->id,
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

        $this->assertDatabaseHas('quotes', [
            'date'        => '2016-10-20',
            'discount'    => 1210,
            'customer_id' => $customer->id,
            'cashed_amount' => 1027,
        ]);

        $this->assertDatabaseHas('lines', [
            'lineable_id'   => Quote::first()->id,
            'lineable_type' => Quote::class,
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);

        $this->assertCount(1, $quote->fresh()->lines);
    }

    /** @test */
    public function date_is_required_when_updating_a_quote()
    {
        $quote = create(Quote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/quotes/{$quote->id}", [
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_updating_a_quote()
    {
        $quote = create(Quote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/quotes/{$quote->id}", [
            'date' => 'NOT VALID DATE',
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function cashed_amount_must_be_numeric_when_updating_a_quote()
    {
        $quote = create(Quote::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/quotes/{$quote->id}", [
            'date' => Carbon::now(),
            'customer_id'   => $customer->id,
            'cashed_amount' => 'NOT VALID',
        ]);
        $this->assertValidationErrors($response, 'cashed_amount');
    }

    /** @test */
    public function customer_is_required_when_updating_a_quote()
    {
        $quote = create(Quote::class);
        $response = $this->json('PATCH', "api/quotes/{$quote->id}", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function it_deletes_a_quote_with_lines()
    {
        $quote = create(Quote::class, [
            'date' => '20/10/2016'
        ]);

        create(Line::class, [
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->delete("api/quotes/{$quote->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('quotes', [
            'date'        => '2016-10-20',
        ]);

        $this->assertDatabaseMissing('lines', [
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
        ]);
    }
}