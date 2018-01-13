<?php

use Tests\TestCase;
use Verkoo\Common\Entities\User;
use App\Entities\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReportsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function a_report_shows_the_orders_between_two_dates()
    {
        $order1 = create(Order::class,['date' => '02/01/2017', 'number' => date('y') . '0001', 'serie' => 1]);
        $order2 = create(Order::class,['date' => '01/02/2017', 'number' => date('y') . '0002', 'serie' => 1]);
        $notValidOrder = create(Order::class,['date' => '01/05/2017', 'number' => date('y') . '0050', 'serie' => 1]);

        $this->json('GET',"api/reports/orders", [
            'date_from' => '2017-01-01',
            'date_to' => '2017-02-28',
        ])->assertStatus(200)
            ->assertSee(
                $order1->number
            )->assertSee(
                $order2->number
            )->assertDontSee(
                $notValidOrder->number
            );
    }

    /** @test */
    public function the_from_and_to_dates_must_be_valid()
    {
        $response = $this->json('GET',"api/reports/orders", [
            'date_from' => '',
            'date_to' => '',
        ]);

        $this->assertValidationErrors($response,['date_from', 'date_to']);

        $response = $this->json('GET',"api/reports/orders", [
            'date_from' => '32/12/2017',
            'date_to' => 'INVALID',
        ]);

        $this->assertValidationErrors($response,['date_from', 'date_to']);
    }
}