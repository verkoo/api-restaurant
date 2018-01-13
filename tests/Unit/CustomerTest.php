<?php

namespace Tests\Unit;

use Verkoo\Common\Entities\Address;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DefaultDeliveryNote;
use Verkoo\Common\Entities\DeliveryNote;
use Tests\TestCase;
use App\Entities\Line;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CustomerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_customer_can_have_default_delivery_note()
    {
        $customer = create(Customer::class);

        $this->assertNull($customer->defaultDeliveryNote);

        create(DefaultDeliveryNote::class, ['customer_id' => $customer->id]);

        $this->assertInstanceOf(DefaultDeliveryNote::class, $customer->fresh()->defaultDeliveryNote);
    }

    /** @test */
    public function it_gets_the_default_address()
    {
        $customer = create(Customer::class);
        $address = create(Address::class, [
            'customer_id' => $customer->id,
            'default' => 1,
        ]);
        create(Address::class, [
            'customer_id' => $customer->id,
            'default' => 0,
        ]);

        $this->assertEquals($address->id, $customer->default_address->id);
    }

    /** @test */
    public function it_gets_the_full_address_string()
    {
        $customer = create(Customer::class);
        create(Address::class, [
            'customer_id' => $customer->id,
            'address'     => 'FAKE STREET 123',
            'postcode'    => '30170',
            'city'        => 'MULA',
            'province'    => '30',
            'default' => 1,
        ]);

        $this->assertEquals('FAKE STREET 123, 30170 - MULA (Murcia)', $customer->full_address);
    }

    /** @test */
    public function it_gets_null_object_if_there_is_no_default_address()
    {
        $customer = create(Customer::class);

        $this->assertInstanceOf(Address::class, $customer->default_address);
    }

    /** @test */
    public function it_gets_the_pending_amount_of_delivery_orders_associated_with_the_customer()
    {
        $customer = create(Customer::class);
        $deliveryNote = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
            'cashed_amount' => 10
        ]);
        $anotherDeliveryNote = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
            'cashed_amount' => 20
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
            'quantity'      => 2,
            'price'         => 20,
        ]);

        $this->assertEquals(70, $customer->getPendingAmount());
    }

    /** @test */
    public function given_an_amount_returns_an_array_of_delivery_notes_than_can_be_cashed_ordered_by_oldest()
    {
        $customer = create(Customer::class);
        $deliveryNote = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
            'date'          => '01/02/2017',
            'cashed_amount' => 10
        ]);
        $anotherDeliveryNote = create(DeliveryNote::class, [
            'customer_id'   => $customer->id,
            'date'          => '01/01/2017',
            'cashed_amount' => 20
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
            'quantity'      => 2,
            'price'         => 20,
        ]);
        create(Line::class, [
            'lineable_id'   => $pending->id,
            'lineable_type' => DeliveryNote::class,
            'price'         => 10,
        ]);

        $this->assertEquals([
            'full' => [
                0 => [
                    'number'  => date('y') . '0002',
                    'date'    => '01/01/2017',
                    'pending' => 20,
                    'id' => $anotherDeliveryNote->id,
                ]
            ],
            'partial' => [
                0 => [
                    'number'  => date('y') . '0001',
                    'date'    => '01/02/2017',
                    'pending' => 50,
                    'id' => $deliveryNote->id
                ]
            ],
            'pending' => [
                0 => [
                    'number'  => date('y') . '0003',
                    'date'    => '01/03/2017',
                    'pending' => 10,
                    'id' => $pending->id
                ]
            ]
        ], $customer->getCashableDeliveryNotes(40));
    }

    /** @test */
    public function a_customer_can_cash_some_delivery_notes_given_an_amount_order_by_date()
    {
        $customer = create(Customer::class);

        $three = create(DeliveryNote::class, [
            'customer_id' => $customer->id,
            'date' => '01/04/2017',
        ]);

        $one = create(DeliveryNote::class, [
            'customer_id' => $customer->id,
            'date' => '01/02/2017',
            'cashed_amount' => 10,
        ]);

        $two = create(DeliveryNote::class, [
            'customer_id' => $customer->id,
            'date' => '01/03/2017',
        ]);

        create(Line::class, [
            'lineable_id' => $one->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 2,
            'price' => 10,
        ]);

        create(Line::class, [
            'lineable_id' => $two->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        create(Line::class, [
            'lineable_id' => $three->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $customer->cashDeliveryNotes(15);

        $this->assertEquals(0, $one->fresh()->getPendingAmount());
        $this->assertEquals(5, $two->fresh()->getPendingAmount());
        $this->assertEquals(10, $three->fresh()->getPendingAmount());
    }
}
