<?php

use Tests\TestCase;
use App\Entities\Line;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DeliveryNote;
use Verkoo\Common\Entities\DefaultDeliveryNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiCustomerTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_customers()
    {
        create(Customer::class, ['name' => 'John Doe']);
        $response = $this->get("api/customers");
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'John Doe'
        ]);
    }

    /** @test */
    public function customer_can_return_a_default_delivery_note_with_lines()
    {
        $customer = create(Customer::class);
        create(DefaultDeliveryNote::class, ['customer_id' => $customer->id]);
        $response = $this->get("api/customers");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => [
                'default_delivery_note' => ['lines']
            ]
        ]);
    }

    /** @test */
    public function a_customer_can_get_the_cashable_delivery_notes()
    {
        $customer = create(Customer::class);

        $cashed = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
        ]);
        $deliveryNote = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
            'date'          => '01/02/2017',
        ]);
        $anotherDeliveryNote = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
            'date'          => '01/01/2017',
        ]);
        $pending = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
            'date'          => '01/03/2017',
        ]);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'quantity'      => 3,
            'price'         => 20,
        ]);
        create(Line::class, [
            'lineable_id'   => $anotherDeliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'quantity'      => 1,
            'price'         => 10,
        ]);
        create(Line::class, [
            'lineable_id'   => $pending->id,
            'lineable_type' => DeliveryNote::class,
            'quantity'      => 1,
            'price'         => 10,
        ]);

        $response = $this->json('GET', "api/customers/{$customer->id}/delivery-notes", [
            'amount' => 20
        ]);
        $response->assertStatus(200);

        tap($response->decodeResponseJson(), function($json) {
            $this->assertCount(1, $json['full']);
            $this->assertCount(1, $json['partial']);
            $this->assertCount(1, $json['pending']);
        });
    }

    /** @test */
    public function a_customer_can_cash_the_cashable_delivery_notes()
    {
        $customer = create(Customer::class);

        $delivery = create(DeliveryNote::class, [
            'customer_id' => $customer->id,
        ]);

        create(Line::class, [
            'lineable_id' => $delivery->id,
            'lineable_type' => DeliveryNote::class,
            'price' => 10,
        ]);

        $this->assertEquals(10, $delivery->getPendingAmount());

        $this->json('POST', "api/customers/{$customer->id}/delivery-notes", [
            'amount' => 10
        ])->assertStatus(200);

        $this->assertEquals(0, $delivery->fresh()->getPendingAmount());
    }
}