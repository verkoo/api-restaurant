<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Entities\Zone;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ZonesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_zones()
    {
        factory(Zone::class)->create(['name' => 'Zone 1']);

        $response = $this->get('zones');
        $response->assertSee('Zone 1');
    }

    /** @test */
    public function it_creates_a_new_zone()
    {
        $response = $this->post("zones", [
            'name' => 'Zone 1',
        ]);

        $this->assertDatabaseHas('zones', [
            'name' => 'Zone 1',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_zone()
    {
        $response = $this->json('POST', "zones");

        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_zone()
    {
        $zone = factory(Zone::class)->create();

        $response = $this->patch("zones/{$zone->id}", [
            'name' => 'Zone 1',
        ]);

        $this->assertDatabaseHas('zones', [
            'name' => 'Zone 1',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_zone()
    {
        $zone = factory(Zone::class)->create();

        $response = $this->json('PATCH', "zones/{$zone->id}");

        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_brand()
    {
        $zone = factory(Zone::class)->create();

        $this->json('DELETE', "zones/{$zone->id}");

        $this->assertDatabaseMissing('zones', [
            'id' => $zone->id
        ]);
    }
}
