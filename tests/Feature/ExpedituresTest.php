<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Expediture;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExpedituresTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_expeditures()
    {
        factory(Expediture::class)->create(['name' => 'Gas']);

        $response = $this->get('expeditures');
        $response->assertSee('Gas');
    }

    /** @test */
    public function it_creates_a_new_expediture()
    {
        $response = $this->post("expeditures", [
            'name' => 'Gas',
            'amount' => '12,27',
            'date' => '01/01/2012',
        ]);

        $this->assertDatabaseHas('expeditures', [
            'name' => 'Gas',
            'amount' => 1227,
            'date' => '2012-01-01',
        ]);

        $response->assertRedirect('expeditures');
    }

    /** @test */
    public function it_validates_form_when_creating_an_expediture()
    {
        $response = $this->json('POST', "expeditures");
        $this->assertValidationErrors($response, ['name', 'date']);
    }

    /** @test */
    public function date_must_be_valid_when_creating_an_expediture()
    {
        $response = $this->json('POST', "expeditures", [
            'date' => 'WRONG DATE'
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function expediture_type_must_be_valid_when_creating_an_expediture()
    {
        $response = $this->json('POST', "expeditures", [
            'expediture_type_id' => 9999
        ]);
        $this->assertValidationErrors($response, 'expediture_type_id');
    }

    /** @test */
    public function it_updates_an_expediture()
    {
        $expediture = factory(Expediture::class)->create();

        $response = $this->patch("expeditures/{$expediture->id}", [
            'name' => 'Gas',
            'date' => '01/01/2012',
        ]);

        $this->assertDatabaseHas('expeditures', [
            'name' => 'Gas',
            'date' => '2012-01-01',
            'id'   => $expediture->id
        ]);

        $response->assertRedirect('expeditures');
    }

    /** @test */
    public function it_validates_form_when_updating_an_expediture()
    {
        $expediture = factory(Expediture::class)->create();

        $response = $this->json('PATCH', "expeditures/{$expediture->id}");
        $this->assertValidationErrors($response, ['name', 'date']);
    }

    /** @test */
    public function date_must_be_valid_when_updating_an_expediture()
    {
        $expediture = factory(Expediture::class)->create();

        $response = $this->json('PATCH', "expeditures/{$expediture->id}", [
            'date' => 'WRONG DATE'
        ]);

        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function expediture_type_must_be_valid_when_updating_an_expediture()
    {
        $expediture = factory(Expediture::class)->create();

        $response = $this->json('PATCH', "expeditures/{$expediture->id}", [
            'expediture_type_id' => 9999
        ]);
        $this->assertValidationErrors($response, 'expediture_type_id');
    }

    /** @test */
    public function it_deletes_an_expediture()
    {
        $expediture = factory(Expediture::class)->create();

        $response = $this->json('DELETE', "expeditures/{$expediture->id}");
        $this->assertDatabaseMissing('expeditures', [
            'id' => $expediture->id
        ]);
        $response->assertRedirect('expeditures');

    }

}
