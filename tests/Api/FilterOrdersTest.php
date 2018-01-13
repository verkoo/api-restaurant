<?php

use Tests\TestCase;
use App\Entities\Line;
use Verkoo\Common\Entities\User;
use App\Entities\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FilterOrdersTest extends TestCase
{
    use DatabaseTransactions;

    protected $transformer;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_filters_fully_cashed_orders()
    {
        $notCashed = create(Order::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => (new Order())->getMorphClass(),
            'quantity' => 1,
            'price' => 10,
        ]);

        $document = create(Order::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $document->id,
            'lineable_type' => (new Order())->getMorphClass(),
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/orders", [
            'type' => 'fully-cashed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(1, $json);
            $this->assertEquals('01/01/2015', $json[0]['date']);
        });
    }

    /** @test */
    public function it_filters_not_cashed_quotes()
    {
        $notCashed = create(Order::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => (new Order())->getMorphClass(),
            'quantity' => 1,
            'price' => 10,
        ]);

        $cashed = create(Order::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $cashed->id,
            'lineable_type' => (new Order())->getMorphClass(),
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/orders", [
            'type' => 'not-cashed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(1, $json);
            $this->assertEquals('01/01/2014', $json[0]['date']);
        });
    }

    /** @test */
    public function it_filters_partially_cashed_quotes()
    {
        $notCashed = create(Order::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => Order::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $cashed = create(Order::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $cashed->id,
            'lineable_type' => Order::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $partiallyCashed = create(Order::class, [
            'date' => '01/01/2016',
            'cashed_amount' => 5
        ]);

        create(Line::class, [
            'lineable_id' => $partiallyCashed->id,
            'lineable_type' => (new Order())->getMorphClass(),
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/orders", [
            'type' => 'partially-cashed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(1, $json);
            $this->assertEquals('01/01/2016', $json[0]['date']);
        });
    }

    /** @test */
    public function it_filters_partially_cashed_and_fully_cashed_delivery_notes()
    {
        $notCashed = create(Order::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => Order::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $cashed = create(Order::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $cashed->id,
            'lineable_type' => Order::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $partiallyCashed = create(Order::class, [
            'date' => '01/01/2016',
            'cashed_amount' => 5
        ]);

        create(Line::class, [
            'lineable_id' => $partiallyCashed->id,
            'lineable_type' => Order::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/orders", [
            'type' => 'cashed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(2, $json);
            $this->assertEquals('01/01/2015', $json[0]['date']);
            $this->assertEquals('01/01/2016', $json[1]['date']);
        });
    }
}