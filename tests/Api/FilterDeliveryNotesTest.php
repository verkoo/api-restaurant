<?php

use Verkoo\Common\Entities\DeliveryNote;
use Tests\TestCase;
use App\Entities\Line;
use Verkoo\Common\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FilterDeliveryNotesTest extends TestCase
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
    public function it_filters_billed_delivery_notes()
    {
        create(DeliveryNote::class, [
            'date' => '01/01/2014'
        ]);

        $delivery = create(DeliveryNote::class, [
            'date' => '01/01/2015'
        ]);

        create(Line::class, [
            'lineable_id' => $delivery->id,
            'lineable_type' => DeliveryNote::class,
            'customer_invoice_number' => date('y') . '0012',
        ]);

        $response = $this->json('GET', "api/delivery-notes", [
            'type' => 'billed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(1, $json);
            $this->assertEquals('01/01/2015', $json[0]['date']);
        });
    }

    /** @test */
    public function it_filters_not_billed_delivery_notes()
    {
        create(DeliveryNote::class, [
            'date' => '01/01/2014'
        ]);

        $delivery = create(DeliveryNote::class, [
            'date' => '01/01/2015'
        ]);

        create(Line::class, [
            'lineable_id' => $delivery->id,
            'lineable_type' => DeliveryNote::class,
            'customer_invoice_number' => date('y') . '0012',
        ]);

        $response = $this->json('GET', "api/delivery-notes", [
            'type' => 'not-billed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(1, $json);
            $this->assertEquals('01/01/2014', $json[0]['date']);
        });
    }

    /** @test */
    public function it_filters_fully_cashed_delivery_notes()
    {
        $notCashed = create(DeliveryNote::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $delivery = create(DeliveryNote::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $delivery->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/delivery-notes", [
            'type' => 'fully-cashed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(1, $json);
            $this->assertEquals('01/01/2015', $json[0]['date']);
        });
    }

    /** @test */
    public function it_filters_not_cashed_delivery_notes()
    {
        $notCashed = create(DeliveryNote::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $cashed = create(DeliveryNote::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $cashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/delivery-notes", [
            'type' => 'not-cashed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(1, $json);
            $this->assertEquals('01/01/2014', $json[0]['date']);
        });
    }

    /** @test */
    public function it_filters_partially_cashed_delivery_notes()
    {
        $notCashed = create(DeliveryNote::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $cashed = create(DeliveryNote::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $cashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $partiallyCashed = create(DeliveryNote::class, [
            'date' => '01/01/2016',
            'cashed_amount' => 5
        ]);

        create(Line::class, [
            'lineable_id' => $partiallyCashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/delivery-notes", [
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
        $notCashed = create(DeliveryNote::class, [
            'date' => '01/01/2014'
        ]);

        create(Line::class, [
            'lineable_id' => $notCashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $cashed = create(DeliveryNote::class, [
            'date' => '01/01/2015',
            'cashed_amount' => 10
        ]);

        create(Line::class, [
            'lineable_id' => $cashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $partiallyCashed = create(DeliveryNote::class, [
            'date' => '01/01/2016',
            'cashed_amount' => 5
        ]);

        create(Line::class, [
            'lineable_id' => $partiallyCashed->id,
            'lineable_type' => DeliveryNote::class,
            'quantity' => 1,
            'price' => 10,
        ]);

        $response = $this->json('GET', "api/delivery-notes", [
            'type' => 'cashed'
        ]);

        tap($response->decodeResponseJson(), function ($json) {
            $this->assertCount(2, $json);
            $this->assertEquals('01/01/2015', $json[0]['date']);
            $this->assertEquals('01/01/2016', $json[1]['date']);
        });
    }
}