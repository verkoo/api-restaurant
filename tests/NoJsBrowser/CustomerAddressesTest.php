<?php

use Verkoo\Common\Entities\Address;
use Verkoo\Common\Entities\Customer;
use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class CustomerAddressesTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_customer_has_addresses()
    {
        $customer = factory(Customer::class)->create();
        factory(Address::class)->create([
            'address'     => 'test street',
            'province'    => '30',
            'customer_id' => $customer->id
        ]);

        $this->actingAs($this->adminUser())
            ->visit("customers/{$customer->id}/edit")
            ->see('Direcciones')
            ->see('test street')
            ->see('Murcia');
    }

    /** @test */
    public function a_customer_can_show_the_address_add_form()
    {
        $customer = factory(Customer::class)->create();

        $this->actingAs($this->adminUser())
            ->visit("customers/{$customer->id}/edit")
            ->click('Nueva')
            ->seePageIs("customers/{$customer->id}/addresses/create");
    }
}
