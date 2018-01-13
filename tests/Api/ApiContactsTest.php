<?php

use Verkoo\Common\Entities\Contact;
use Verkoo\Common\Entities\Supplier;
use Verkoo\Common\Entities\User;
use Tests\TestCase;
use Verkoo\Common\Entities\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiContactsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = create(User::class);
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_contacts()
    {
        $this->disableExceptionHandling();
        create(Contact::class, [
            'name' => 'John Doe',
            'phone' => '12345',
            'phone2' => '67890',
            'email' => 'john@example.com',
        ]);

        $response = $this->get("api/contacts");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'John Doe',
            'phone' => '12345',
            'phone2' => '67890',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_filters_the_contacts_by_name()
    {
        create(Contact::class, [
            'name' => 'John Doe',
        ]);

        create(Contact::class, [
            'name' => 'Jane Doe',
        ]);

        $response = $this->json('GET', "api/contacts", [
            'search' => 'Ja'
        ]);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'Jane Doe',
        ]);

        $this->assertCount(1, $response->json());
    }

    /** @test */
    public function it_filters_the_contacts_by_name_mergin_also_customers_and_suppliers()
    {
        $this->disableExceptionHandling();
        create(Contact::class, [
            'name' => 'Contact One',
        ]);

        create(Contact::class, [
            'name' => 'Contact Two',
        ]);

        create(Customer::class, [
            'name' => 'Customer One',
        ]);

        create(Customer::class, [
            'name' => 'Customer Two',
        ]);

        create(Supplier::class, [
            'name' => 'Supplier One',
        ]);

        create(Supplier::class, [
            'name' => 'Supplier Two',
        ]);


        $response = $this->json('GET', "api/contacts", [
            'search' => 'One'
        ]);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'Contact One',
        ]);

        $response->assertJsonFragment([
            'name' => 'Customer One',
        ]);

        $response->assertJsonFragment([
            'name' => 'Supplier One',
        ]);

        $this->assertCount(3, $response->json());
    }

    /** @test */
    public function it_creates_a_new_contact()
    {
        $response = $this->post("api/contacts", [
            'name' => 'John Doe',
            'phone' => '12345',
            'phone2' => '67890',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'name' => 'John Doe',
            'phone' => '12345',
            'phone2' => '67890',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function name_is_required_when_creating_a_contact()
    {
        $response = $this->json('POST', "api/contacts", [
            'phone' => '12345',
            'phone2' => '67890',
            'email' => 'john@example.com',
        ]);
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_contact()
    {
        $this->disableExceptionHandling();
        $contact = create(Contact::class, [
            'name' => 'John Doe',
            'phone' => '12345',
            'phone2' => '67890',
            'email' => 'john@example.com',
        ]);

        $response = $this->patch("api/contacts/{$contact->id}", [
            'name' => 'Jane Doe',
            'phone' => '67890',
            'phone2' => '12345',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'name' => 'Jane Doe',
            'phone' => '67890',
            'phone2' => '12345',
            'email' => 'jane@example.com',
        ]);
    }

    /** @test */
    public function date_is_required_when_updating_a_contact()
    {
        $contact = create(Contact::class);
        $response = $this->json('PATCH', "api/contacts/{$contact->id}", [
            'phone' => '67890',
            'phone2' => '12345',
            'email' => 'jane@example.com',
        ]);
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_contact_with_lines()
    {
        $contact = create(Contact::class, [
            'name' => 'John Doe'
        ]);

        $response = $this->delete("api/contacts/{$contact->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('contacts', [
            'name' => 'John Doe'
        ]);
    }
}