<?php

use Verkoo\Common\Entities\Customer;
use Tests\TestCase;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiProductCustomersTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_adds_a_customer_to_a_product()
    {
        $product = create(Product::class);
        $customer = create(Customer::class);

        $response = $this->post("api/products/{$product->id}/customers",[
            'customer_id' => $customer->id,
            'price' => '10,21',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('customer_product', [
            'customer_id' => $customer->id,
            'price' => 1021,
        ]);
    }

    /** @test */
    public function a_customer_can_not_be_added_if_already_exists_in_a_product()
    {
        $product = create(Product::class);
        $customer = create(Customer::class);

        $product->customers()->attach($customer->id);

        $this->assertCount(1,$product->customers);

        $this->post("api/products/{$product->id}/customers",[
            'customer_id' => $customer->id,
        ]);

        $this->assertCount(1,$product->customers);
    }

    /** @test */
    public function it_deletes_a_customer_from_a_product()
    {
        $product = create(Product::class);
        $customer = create(Customer::class);

        $product->customers()->attach($customer->id);

        $response = $this->delete("api/products/{$product->id}/customers/{$customer->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('customer_product', [
            'product_id'  => $product->id,
            'customer_id' => $customer->id,
        ]);
    }
}