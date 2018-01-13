<?php

namespace Tests\Feature;

use App\Entities\Kitchen;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class KitchensTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_kitchens()
    {
        factory(Kitchen::class)->create(['name' => 'Kitchen 1']);

        $response = $this->get('kitchens');
        $response->assertSee('Kitchen 1');
    }

    /** @test */
    public function it_creates_a_new_kitchen()
    {
        $response = $this->post("kitchens", [
            'name' => 'Main Kitchen',
            'printer' => 'LPT2',
        ]);

        $this->assertDatabaseHas('kitchens', [
            'name' => 'Main Kitchen',
            'printer' => 'LPT2',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating()
    {
        $response = $this->json('POST', "kitchens");
        $this->assertValidationErrors($response,'name');

    }

    /** @test */
    public function it_updates_a_kitchen()
    {
        $kitchen = factory(Kitchen::class)->create();

        $response = $this->patch("kitchens/{$kitchen->id}", [
            'name' => 'Main Kitchen',
            'printer' => 'LPT2',
        ]);

        $this->assertDatabaseHas('kitchens', [
            'name' => 'Main Kitchen',
            'printer' => 'LPT2',
            'id' => $kitchen->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_kitchen()
    {
        $kitchen = factory(Kitchen::class)->create();

        $response = $this->json('PATCH', "kitchens/{$kitchen->id}");
        $this->assertValidationErrors($response,'name');
    }

    /** @test */
    public function it_deletes_a_kitchen()
    {
        $kitchen = factory(Kitchen::class)->create();

        $this->json('DELETE', "kitchens/{$kitchen->id}");
        $this->assertDatabaseMissing('kitchens',[
            'id' => $kitchen->id
        ]);
    }
}
