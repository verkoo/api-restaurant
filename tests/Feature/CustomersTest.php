<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Customer;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomersTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_customers()
    {
        factory(Customer::class)->create(['name' => 'Customer 1']);

        $response = $this->get('customers');
        $response->assertSee('Customer 1');
    }

    /** @test */
    public function it_creates_a_new_customer()
    {
        $response = $this->post("customers", [
            'name'           => 'John Doe',
            'dni'            => '12345678A',
            'phone'          => '968660000',
            'phone2'         => '968660001',
            'contact_person' => 'John Doe',
            'email'          => 'john@example.com',
            'comments'       => 'Some comments',
        ]);

        $this->assertDatabaseHas('customers', [
            'name'           => 'John Doe',
            'dni'            => '12345678A',
            'phone'          => '968660000',
            'phone2'         => '968660001',
            'contact_person' => 'John Doe',
            'email'          => 'john@example.com',
            'comments'       => 'Some comments',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_customer()
    {
        $response = $this->json('POST', "customers");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function dni_must_be_unique()
    {
        factory(Customer::class)->create(['dni' => '123']);
        $response = $this->json('POST', "customers", [
            'dni' => '123',
        ]);

        $this->assertValidationErrors($response, 'dni');
    }

    /** @test */
    public function dni_only_takes_the_first_9_characters()
    {
        factory(Customer::class)->create(['dni' => '123456789']);
        $response = $this->json('POST', "customers", [
            'dni' => '1234567890',
        ]);

        $this->assertValidationErrors($response, 'dni');
    }

    /** @test */
    public function it_updates_a_product()
    {
        $customer = factory(Customer::class)->create();

        $response = $this->json("PATCH", "customers/{$customer->id}", [
            'name'     => 'John Doe',
            'dni'      => '12345678A',
            'phone'    => '968660000',
            'phone2'         => '968660001',
            'contact_person' => 'John Doe',
            'email'    => 'john@example.com',
            'comments' => 'Some comments',
        ]);

        $this->assertDatabaseHas('customers', [
            'name'     => 'John Doe',
            'dni'      => '12345678A',
            'phone'    => '968660000',
            'phone2'         => '968660001',
            'contact_person' => 'John Doe',
            'email'    => 'john@example.com',
            'comments' => 'Some comments',
            'id'       => $customer->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_customer()
    {
        $customer = factory(Customer::class)->create();

        $response = $this->json('PATCH', "customers/{$customer->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_customer()
    {
        $customer = factory(Customer::class)->create();

        $this->json('DELETE', "customers/{$customer->id}");

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}
