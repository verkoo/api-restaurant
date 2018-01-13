<?php

use Tests\TestCase;
use App\Entities\Order;
use Verkoo\Common\Entities\Box;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Payment;
use Verkoo\Common\Entities\Session;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiSessionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_gets_the_active_sessions_for_the_auth_user()
    {
        $user = create(User::class);
        $box = create(Box::class,[
            'name'           => 'General',
            'description'    => 'Caja General',
        ]);
        factory(Session::class,2)->create(['open' => true]);
        factory(Session::class)->create(['open' => true, 'box_id' => $box->id]);

        $box->addUser($user);
        $this->actingAs($user, 'api');

        $response = $this->get("api/sessions");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name'           => 'General',
            'description'    => 'Caja General',
            'hasOpenSession' => true,
        ]);
    }

    /** @test */
    public function it_returns_the_active_orders_in_the_active_session()
    {
        $user = create(User::class);
        $this->actingAs($user, 'api');

        $session = factory(Session::class)->create();
        factory(Order::class)->times(3)->create();
        $payment = factory(Payment::class)->create();

        $orders = factory(Order::class)->times(2)->create(['payment_id' => null, 'session_id' => $session->id]);
        factory(Order::class)->times(2)->create(['payment_id' => $payment->id, 'session_id' => $session->id]);
        
        $response = $this->get("api/sessions/{$session->id}");

        $response->assertStatus(200)
            ->assertJson($orders->toArray());
    }

    /** @test */
    public function it_deletes_not_cashed_orders_in_a_given_session()
    {
        $user = create(User::class);
        $this->actingAs($user, 'api');

        $session = factory(Session::class)->create();
        factory(Order::class)->times(3)->create();
        $payment = factory(Payment::class)->create();
        factory(Options::class)->create(['payment_id' => $payment->id]);

        factory(Order::class)->times(2)->create(['payment_id' => null, 'session_id' => $session->id]);
        factory(Order::class)->times(2)->create(['payment_id' => $payment->id, 'session_id' => $session->id]);

        $response = $this->delete("api/sessions/{$session->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['ok' => true]);

        $orders = Order::whereSessionId($session->id)->notCashed()->get();
        $this->assertCount(0, $orders);
    }
}