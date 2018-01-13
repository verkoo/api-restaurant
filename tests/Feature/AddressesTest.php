<?php

namespace Tests\Feature;

use Tests\TestCase;
use Verkoo\Common\Entities\Address;
use Verkoo\Common\Entities\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AddressesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_addresses()
    {
        $customer = create(Customer::class);
        factory(Address::class)->create([
            'address' => 'FAKE ADDRESS',
            'customer_id' => $customer->id,
        ]);

        $response = $this->get("customers/{$customer->id}/edit");
        $response->assertSee('FAKE ADDRESS');
    }

    /** @test */
    public function it_creates_a_new_address()
    {
        $customer = create(Customer::class);

        $response = $this->post("customers/{$customer->id}/addresses", [
            'address' => 'FAKE ADDRESS',
            'city' => 'MULA',
            'postcode' => '30170',
            'province' => '30',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address' => 'FAKE ADDRESS',
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_an_address()
    {
        $customer = create(Customer::class);

        $response = $this->json('POST', "customers/{$customer->id}/addresses");
        $this->assertValidationErrors($response, ['address', 'city', 'postcode', 'province']);
    }

    /** @test */
    public function it_updates_an_address()
    {
        $customer = create(Customer::class);
        $address = create(Address::class, ['customer_id' => $customer->id]);

        $response = $this->json("PATCH", "customers/{$customer->id}/addresses/{$address->id}", [
            'address' => 'FAKE ADDRESS',
            'city' => 'MULA',
            'postcode' => '30170',
            'province' => '30',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address' => 'FAKE ADDRESS',
            'city' => 'MULA',
            'postcode' => '30170',
            'province' => '30',
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_updates_the_default_address_to_false_if_null_is_sent()
    {
        $customer = create(Customer::class);
        $address = create(Address::class, [
            'customer_id' => $customer->id,
            'default' => 1,
        ]);

        $response = $this->json("PATCH", "customers/{$customer->id}/addresses/{$address->id}", [
            'address' => 'FAKE ADDRESS',
            'city' => 'MULA',
            'postcode' => '30170',
            'province' => '30',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address' => 'FAKE ADDRESS',
            'city' => 'MULA',
            'postcode' => '30170',
            'province' => '30',
            'default' => 0,
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_box()
    {
        $customer = create(Customer::class);
        $address = create(Address::class, ['customer_id' => $customer->id]);

        $response = $this->json('PATCH', "customers/{$customer->id}/addresses/{$address->id}");
        $this->assertValidationErrors($response, ['address', 'city', 'postcode', 'province']);
    }

    /** @test */
    public function it_deletes_an_address()
    {
        $customer = create(Customer::class);
        $address = create(Address::class, ['customer_id' => $customer->id]);

        $this->json('DELETE', "customers/{$customer->id}/addresses/{$address->id}");
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id
        ]);
    }
}