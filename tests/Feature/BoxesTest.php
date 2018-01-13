<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Box;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BoxesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_boxes()
    {
        factory(Box::class)->create(['name' => 'Box 1']);

        $response = $this->get('boxes');
        $response->assertSee('Box 1');
    }

    /** @test */
    public function it_creates_a_new_box()
    {
        $response = $this->post("boxes", [
            'name' => 'Box 1'
        ]);

        $this->assertDatabaseHas('boxes', [
            'name' => 'Box 1',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_box()
    {
        $response = $this->json('POST', "boxes");
        $this->assertValidationErrors($response, 'name');
    }


    /** @test */
    public function it_updates_a_box()
    {
        $box = factory(Box::class)->create();

        $response = $this->json("PATCH", "boxes/{$box->id}", [
            'name' => 'Edit Name'
        ]);

        $this->assertDatabaseHas('boxes', [
            'name' => 'Edit Name',
            'id' => $box->id
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_box()
    {
        $box = factory(Box::class)->create();

        $response = $this->json('PATCH', "boxes/{$box->id}");
        $this->assertValidationErrors($response, 'name');
    }


    /** @test */
    public function it_deletes_a_box()
    {
        $box = factory(Box::class)->create();

        $this->json('DELETE', "boxes/{$box->id}");
        $this->assertDatabaseMissing('boxes', [
            'id' => $box->id
        ]);
    }
}