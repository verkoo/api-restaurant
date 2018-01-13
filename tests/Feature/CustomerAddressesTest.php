<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Address;
use Verkoo\Common\Entities\Customer;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerAddressesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_validates_form_when_creating_an_address()
    {
        $customer = factory(Customer::class)->create();

        $response = $this->json('POST', "customers/{$customer->id}/addresses");
        $this->assertValidationErrors($response, [
            'address',
            'postcode',
            'city',
            'province',
        ]);
    }

    /** @test */
    public function a_customer_can_add_an_address()
    {
        $customer = factory(Customer::class)->create();

        $response = $this->post("customers/{$customer->id}/addresses", [
            'address'  => 'Dirección 123',
            'postcode' => '30001',
            'city'     => 'Murcia',
            'province' => '30',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address'  => 'Dirección 123',
            'postcode' => '30001',
            'city'     => 'Murcia',
            'province' => '30',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_an_address()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make();
        $customer->addresses()->save($address);


        $response = $this->json('PATCH', "customers/{$customer->id}/addresses/{$address->id}");
        $this->assertValidationErrors($response, [
            'address',
            'postcode',
            'city',
            'province',
        ]);
    }

    /** @test */
    public function a_customer_can_update_an_address()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make(['default' => false]);
        $customer->addresses()->save($address);


        $response = $this->patch("customers/{$customer->id}/addresses/{$address->id}", [
            'address'  => 'Dirección 123',
            'postcode' => '30001',
            'city'     => 'Murcia',
            'province' => '30',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address'  => 'Dirección 123',
            'postcode' => '30001',
            'city'     => 'Murcia',
            'province' => '30',
            'id'       => $address->id
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function a_customer_can_delete_an_address()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make(['default' => false]);
        $customer->addresses()->save($address);


        $response = $this->delete("customers/{$customer->id}/addresses/{$address->id}");

        $this->assertDatabaseMissing('addresses', [
            'id'  => $address->id,
        ]);

        $response->assertStatus(302);
    }

}
