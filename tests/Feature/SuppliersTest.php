<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Supplier;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SuppliersTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_suppliers()
    {
        factory(Supplier::class)->create(['name' => 'Amazon']);

        $response = $this->get('suppliers');
        $response->assertSee('Amazon');
    }

    /** @test */
    public function it_creates_a_new_supplier()
    {
        $response = $this->post("suppliers", [
            'name' => 'Amazon',
            'phone' => '123456',
            'phone2' => '123456789',
            'contact' => 'John',
            'email' => 'john@example.com',
            'address' => 'Fake Street',
            'web' => 'www.amazon.com',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Amazon',
            'phone' => '123456',
            'phone2' => '123456789',
            'contact' => 'John',
            'email' => 'john@example.com',
            'address' => 'Fake Street',
            'web' => 'www.amazon.com',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_supplier()
    {
        $response = $this->json('POST', "suppliers");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function email_must_be_valid()
    {
        $response = $this->json('POST', "suppliers", [
            'email' => 'WRONG EMAIL'
        ]);

        $this->assertValidationErrors($response, 'email');
    }

    /** @test */
    public function it_updates_a_supplier()
    {
        $supplier = factory(Supplier::class)->create();

        $response = $this->patch("suppliers/{$supplier->id}", [
            'name' => 'Amazon',
            'phone' => '123456',
            'phone2' => '123456789',
            'contact' => 'John',
            'email' => 'john@example.com',
            'address' => 'Fake Street',
            'web' => 'www.amazon.com',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Amazon',
            'phone' => '123456',
            'phone2' => '123456789',
            'contact' => 'John',
            'email' => 'john@example.com',
            'address' => 'Fake Street',
            'web' => 'www.amazon.com',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating()
    {
        $supplier = factory(Supplier::class)->create();

        $response = $this->json('PATCH', "suppliers/{$supplier->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_brand()
    {
        $supplier = factory(Supplier::class)->create();

        $this->json('DELETE', "suppliers/{$supplier->id}");
        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id
        ]);
    }
}
