<?php

namespace Tests\Feature;

use App\Entities\Extra;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExtrasTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_extras()
    {
        factory(Extra::class)->create(['name' => 'Extra 1']);

        $response = $this->get('extras');
        $response->assertSee('Extra 1');
    }

    /** @test */
    public function it_creates_a_new_extra()
    {
        $response = $this->post("extras", [
            'name' => 'Ketchup',
            'price' => '21,56',
        ]);

        $this->assertDatabaseHas('extras', [
            'name' => 'Ketchup',
            'price' => 2156,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_an_extra()
    {
        $response = $this->json('POST', "extras");

        $this->assertValidationErrors($response, [
            'name',
            'price',
        ]);
    }

    /** @test */
    public function it_updates_an_extra()
    {
        $extra = factory(Extra::class)->create();

        $response = $this->json("PATCH", "extras/{$extra->id}", [
            'name' => 'John Doe',
            'price' => '12,34',
        ]);

        $this->assertDatabaseHas('extras', [
            'name' => 'John Doe',
            'price' => 1234,
            'id'     => $extra->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating()
    {
        $extra = factory(Extra::class)->create();

        $response = $this->json('PATCH', "extras/{$extra->id}");
        $this->assertValidationErrors($response, [
            'name',
            'price',
        ]);
    }

    /** @test */
    public function it_deletes_an_extra()
    {
        $extra = factory(Extra::class)->create();

        $this->json('DELETE', "extras/{$extra->id}");
        $this->assertDatabaseMissing('extras', [
            'id' => $extra->id
        ]);
    }
}
