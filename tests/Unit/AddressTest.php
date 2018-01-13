<?php

use Verkoo\Common\Entities\Address;
use Verkoo\Common\Entities\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */

    public function it_can_get_the_name_of_the_province()
    {
        $address = factory(Address::class)->make(['province' => '30']);

        $this->assertEquals($address->provinceName, 'Murcia');
    }

    /** @test */

    public function it_sets_to_default_false_the_rest_of_the_addresses_when_adding_a_new_default_true_one()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make(['address' => 'shouldBeFalse']);
        $address2 = factory(Address::class)->make(['address' => 'shouldBeFalseToo']);
        $address3 = factory(Address::class)->make(['address' => 'shouldBeTrue']);

        $customer->addresses()->save($address);
        $customer->addresses()->save($address2);
        $customer->addresses()->save($address3);

        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'address' => 'shouldBeFalse',
            'default' => 0
        ]);
        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'address' => 'shouldBeFalseToo',
            'default' => 0
        ]);
        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'address' => 'shouldBeTrue',
            'default' => 1
        ]);
    }

    /** @test */

    public function nothing_happens_if_there_are_more_addresses_and_create_a_new_default_false_one()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make(['address' => 'shouldBeTrue']);
        $address2 = factory(Address::class)->make(['address' => 'shouldBeFalse', 'default' => false]);

        $customer->addresses()->save($address);
        $customer->addresses()->save($address2);

        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'address' => 'shouldBeTrue',
            'default' => 1
        ]);
        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'address' => 'shouldBeFalse',
            'default' => 0
        ]);
    }

    /** @test */
    public function it_sets_to_default_true_if_it_is_the_fist_address_of_the_customer_and_checks_true()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make(['address' => 'shouldBeTrue']);
        $customer->addresses()->save($address);
        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'address' => 'shouldBeTrue',
            'default' => 1
        ]);
    }

    /** @test */
    public function it_sets_to_default_true_if_it_is_the_fist_address_of_the_customer_and_checks_false()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make(['address' => 'shouldBeTrue', 'default' => false]);
        $customer->addresses()->save($address);
        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'address' => 'shouldBeTrue',
            'default' => 1
        ]);
    }

    

    /** @test */

    public function first_address_is_set_as_default_when_default_is_deleted()
    {
        $customer = factory(Customer::class)->create();
        $address = factory(Address::class)->make(['address' => 'first']);
        $address2 = factory(Address::class)->make(['address' => 'second']);
        $address3 = factory(Address::class)->make(['address' => 'third']);

        $customer->addresses()->save($address);
        $customer->addresses()->save($address2);
        $customer->addresses()->save($address3);

        $addressToDelete = Address::where('default', true)->first();
        $addressToDelete->delete();

        $newDefaultAddress = $customer->addresses()->first();
        $this->assertTrue( !! $newDefaultAddress->default);
    }
}