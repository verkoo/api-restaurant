<?php

namespace Tests\Feature;

use Tests\TestCase;
use Verkoo\Common\Entities\Tax;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Payment;
use Verkoo\Common\Entities\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OptionsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function an_admin_can_update_the_config()
    {
        $options  = create(Options::class);
        $payment  = create(Payment::class);
        $customer = create(Customer::class);
        $tax      = create(Tax::class);

        $admin = $this->adminUser();
        \Gate::define('update_settings', function ($admin) {
            return true;
        });

        $this->actingAs($admin);

        $response = $this->patch("options/{$options->id}", [
            'company_name' => 'Apple',
            'address' => 'Infinite Loop',
            'cif' => '12345678',
            'cp' => '30170',
            'city' => 'Cupertino',
            'phone' => '0000000',
            'web' => 'www.apple.es',
            'default_printer' => 'LPT1',
            'open_drawer_when_cash' => 0,
            'print_ticket_when_cash' => 1,
            'hide_out_of_stock' => 1,
            'show_stock_in_photo' => 1,
            'recount_stock_when_open_cash' => 1,
            'break_down_taxes_in_ticket' => 1,
            'cash_pending_ticket' => 1,
            'default_tpv_serie' => 2,
            'manage_kitchens' => 1,
            'pagination' => 10,
            'tax_id' => $tax->id,
            'cash_customer' => $customer->id,
            'payment_id' => $payment->id,
        ]);

        $this->assertDatabaseHas('options', [
            'company_name' => 'Apple',
            'address' => 'Infinite Loop',
            'cif' => '12345678',
            'cp' => '30170',
            'city' => 'Cupertino',
            'phone' => '0000000',
            'web' => 'www.apple.es',
            'default_printer' => 'LPT1',
            'open_drawer_when_cash' => 0,
            'print_ticket_when_cash' => 1,
            'hide_out_of_stock' => 1,
            'show_stock_in_photo' => 1,
            'recount_stock_when_open_cash' => 1,
            'break_down_taxes_in_ticket' => 1,
            'cash_pending_ticket' => 1,
            'default_tpv_serie' => 2,
            'manage_kitchens' => 1,
            'pagination' => 10,
            'tax_id' => $tax->id,
            'cash_customer' => $customer->id,
            'payment_id' => $payment->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function a_no_admin_user_cannot_update_the_config()
    {
        $options = factory(Options::class)->create();

        $user = factory(User::class)->create();

        $this->actingAs($user);

        $response = $this->put("/options/{$options->id}");
        $response->assertStatus(302);
    }
}
