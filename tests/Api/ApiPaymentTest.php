<?php

use Verkoo\Common\Entities\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiPaymentTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_payments()
    {
        $payments = factory(Payment::class,3)->create();

        $response = $this->get("api/payments");

        $response->assertStatus(200)
            ->assertJson(
                $payments->toArray()
        );
    }
}