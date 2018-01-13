<?php

namespace Tests\Feature;

use Tests\TestCase;
use Verkoo\Common\Entities\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaymentsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_payments()
    {
        factory(Payment::class)->create(['name' => 'Payment 1']);

        $response = $this->get('payments');
        $response->assertSee('Payment 1');
    }

    /** @test */
    public function it_creates_a_new_payment()
    {
        $response = $this->post("payments", [
            'name' => 'Contado',
        ]);

        $this->assertDatabaseHas('payments', [
            'name' => 'Contado',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_payment()
    {
        $response = $this->json('POST', "payments");

        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_payment()
    {
        $payment = factory(Payment::class)->create();

        $response = $this->json("PATCH", "payments/{$payment->id}", [
            'name' => 'Contado',
        ]);

        $this->assertDatabaseHas('payments', [
            'name' => 'Contado',
            'id'     => $payment->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating()
    {
        $payment = factory(Payment::class)->create();

        $response = $this->json('PATCH', "payments/{$payment->id}");

        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_payment()
    {
        $payment = factory(Payment::class)->create();

        $this->json('DELETE', "payments/{$payment->id}");

        $this->assertDatabaseMissing('payments', [
            'id' => $payment->id
        ]);
    }
}
