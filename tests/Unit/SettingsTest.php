<?php

use Tests\TestCase;
use Verkoo\Common\Entities\Tax;
use Verkoo\Common\Entities\Payment;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Settings;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_returns_the_settings_from_the_cache()
    {
        $settings = factory(Options::class)->create(['company_name' => 'Test Company']);

        $this->assertEquals(Settings::get('company_name'), 'Test Company');

        $settings->company_name = 'Name changed';
        $settings->save();

        $this->assertEquals(Settings::get('company_name'), 'Test Company');
    }

    /** @test */
    public function it_flushes_the_cache_when_admin_updates_the_settings()
    {
        $admin = $this->adminUser();
        \Gate::define('update_settings', function ($admin) {
            return true;
        });

        $settings = factory(Options::class)->create(['company_name' => 'Test Company']);

        $this->actingAs($admin);

        $this->assertEquals('Test Company',Settings::get('company_name'));

        $payment = create(Payment::class);
        $tax = create(Tax::class);
        $this->put("/options/{$settings->id}", [
            'company_name' => 'Name Changed',
            'payment_id' => $payment->id, //Pass Validation
            'tax_id' => $tax->id, //Pass Validation
        ]);

        $this->assertEquals('Name Changed', Settings::get('company_name'));
    }
}