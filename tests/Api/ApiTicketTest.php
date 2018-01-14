<?php

use App\Entities\Line;
use App\Entities\Order;
use App\Entities\Kitchen;
use App\Events\OrderSentToKitchen;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Events\OrderCashed;
use Verkoo\Common\Events\TicketButtonPressed;
use Verkoo\Common\Factories\TicketFactory;
use Verkoo\Common\Tickets\CashRecipeTicket;
use Verkoo\Common\Tickets\CashTicket;
use Verkoo\Common\Tickets\KitchenTicket;
use Verkoo\Common\Tickets\OpenDrawerTicket;
use Verkoo\Common\Tickets\ProformaTicket;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiTicketTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_prints_lines_when_order_has_lines()
    {
        $ticketSpy = Mockery::spy(ProformaTicket::class);
        $ticketFactory = Mockery::mock(TicketFactory::class, ['createProforma' => $ticketSpy]);
        app()->instance(TicketFactory::class, $ticketFactory);

        $order = factory(Order::class)->create();
        event(new TicketButtonPressed($order));

        $ticketSpy->shouldHaveReceived('printTicket')->once();
    }

    /** @test */
    public function it_prints_a_ticket_when_press_cash_button_event_is_fired_and_settings_allow_it()
    {
        $ticketSpy = Mockery::spy(CashTicket::class);
        $drawerSpy = Mockery::spy(OpenDrawerTicket::class);

        factory(Options::class)->create([
            'print_ticket_when_cash' => 1,
            'open_drawer_when_cash' => 1
        ]);

        $ticketFactory = Mockery::mock(TicketFactory::class, [
            'createTicket' => $ticketSpy,
            'createOpenDrawerTicket' => $drawerSpy
        ]);

        app()->instance(TicketFactory::class, $ticketFactory);

        $order = factory(Order::class)->create();
        event(new OrderCashed($order));

        $ticketSpy->shouldHaveReceived('printTicket')->once();
        $drawerSpy->shouldHaveReceived('printTicket')->once();
    }

    /** @test */
    public function it_does_nothing_when_press_cash_button_event_is_fired_and_settings_deny_it()
    {
        $ticketSpy = Mockery::spy(CashTicket::class);
        $drawerSpy = Mockery::spy(OpenDrawerTicket::class);

        factory(Options::class)->create([
            'print_ticket_when_cash' => 0,
            'open_drawer_when_cash' => 0
        ]);

        $order = factory(Order::class)->create();
        event(new OrderCashed($order));

        $ticketSpy->shouldNotHaveReceived('printTicket');
        $drawerSpy->shouldNotHaveReceived('printTicket');

    }
    
    /** @test */
    public function it_prints_a_ticket_when_order_is_sent_to_kitchen_if_the_line_has_a_kitchen_and_pending_orders()
    {
        $ticketSpy = Mockery::spy(KitchenTicket::class);
        $ticketFactory = Mockery::mock(TicketFactory::class, ['createKitchenTicket' => $ticketSpy]);
        app()->instance(TicketFactory::class, $ticketFactory);

        $kitchen = factory(Kitchen::class)->create();
        $line = factory(Line::class)->make(['kitchen_id' => $kitchen->id, 'quantity' => 2, 'ordered' => 1]);
        $order = factory(Order::class)->create();
        $order->lines()->save($line);

        event(new OrderSentToKitchen($order));

        $ticketSpy->shouldHaveReceived('printTicket')->once();
    }

    /** @test */
    public function it_prints_two_tickets_when_order_is_sent_to_kitchen_if_two_lines_have_different_kitchens_and_pending_orders()
    {
        $ticketSpy = Mockery::spy(KitchenTicket::class);
        $ticketFactory = Mockery::mock(TicketFactory::class, ['createKitchenTicket' => $ticketSpy]);
        app()->instance(TicketFactory::class, $ticketFactory);

        $kitchen = factory(Kitchen::class)->create();
        $secondKitchen = factory(Kitchen::class)->create();
        $line = factory(Line::class)->make(['kitchen_id' => $kitchen->id, 'quantity' => 2, 'ordered' => 1]);
        $secondLine = factory(Line::class)->make(['kitchen_id' => $secondKitchen->id, 'quantity' => 2, 'ordered' => 1]);
        $order = factory(Order::class)->create();
        $order->lines()->save($line);
        $order->lines()->save($secondLine);

        event(new OrderSentToKitchen($order));

        $ticketSpy->shouldHaveReceived('printTicket')->twice();
    }

    /** @test */
    public function it_prints_no_tickets_when_order_is_sent_to_kitchen_and_does_not_have_pending_orders()
    {
        $ticketSpy = Mockery::spy(KitchenTicket::class);
        $ticketFactory = Mockery::mock(TicketFactory::class, ['createKitchenTicket' => $ticketSpy]);
        app()->instance(TicketFactory::class, $ticketFactory);

        $kitchen = factory(Kitchen::class)->create();
        $line = factory(Line::class)->make(['kitchen_id' => $kitchen->id, 'quantity' => 2, 'ordered' => 2]);
        $order = factory(Order::class)->create();
        $order->lines()->save($line);

        event(new OrderSentToKitchen($order));

        $ticketSpy->shouldNotHaveReceived('printTicket');
    }

    /** @test */
    public function it_prints_no_tickets_when_order_is_sent_to_kitchen_and_line_does_not_have_kitchen()
    {
        $ticketSpy = Mockery::spy(KitchenTicket::class);
        $ticketFactory = Mockery::mock(TicketFactory::class, ['createKitchenTicket' => $ticketSpy]);
        app()->instance(TicketFactory::class, $ticketFactory);

        $line = factory(Line::class)->make(['kitchen_id' => null, 'quantity' => 2, 'ordered' => 0]);
        $order = factory(Order::class)->create();
        $order->lines()->save($line);

        event(new OrderSentToKitchen($order));

        $ticketSpy->shouldNotHaveReceived('printTicket');
    }

    /** @test */
    public function it_opens_the_drawer_when_press_open_drawer_button()
    {
        $ticketSpy = Mockery::spy(OpenDrawerTicket::class);
        $ticketFactory = Mockery::mock(TicketFactory::class, ['createOpenDrawerTicket' => $ticketSpy]);
        app()->instance(TicketFactory::class, $ticketFactory);

        $this->post('/api/open-drawer');

        $ticketSpy->shouldHaveReceived('printTicket')->once();
    }

    /** @test */
    public function amount_is_required_when_printing_cash_recipe()
    {
        $response = $this->json('post', '/api/cash-recipe', [
            'items' => [0 => 1],
            'customer' => 1,
        ]);

        $this->assertValidationErrors($response, 'amount');
    }

    /** @test */
    public function items_are_required_when_printing_cash_recipe()
    {
        $response = $this->json('post', '/api/cash-recipe', [
            'amount' => 10,
            'customer' => 1,
        ]);

        $this->assertValidationErrors($response, 'items');
    }

    /** @test */
    public function customer_is_required_when_printing_cash_recipe()
    {
        $response = $this->json('post', '/api/cash-recipe', [
            'amount' => 10,
            'items' => [0 => 1]
        ]);

        $this->assertValidationErrors($response, 'customer');
    }

    /** @test */
    public function pending_is_required_when_printing_cash_recipe()
    {
        $response = $this->json('post', '/api/cash-recipe', [
            'amount' => 10,
            'customer' => 1,
            'items' => [0 => 1]
        ]);

        $this->assertValidationErrors($response, 'pending');
    }

    /** @test */
    public function it_prints_the_cash_recipe_when_hit_the_endpoint()
    {
        $ticketSpy = Mockery::spy(CashRecipeTicket::class);
        $ticketFactory = Mockery::mock(TicketFactory::class, ['createCashRecipeTicket' => $ticketSpy]);
        app()->instance(TicketFactory::class, $ticketFactory);

        $this->post('/api/cash-recipe', [
            'amount' => 10,
            'customer' => 1,
            'pending' => 1,
            'items' => [0 => 1]
        ]);

        $ticketSpy->shouldHaveReceived('printTicket')->once();
    }
}