<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Brand;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BrandsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_brands()
    {
        factory(Brand::class)->create(['name' => 'Brand 1']);

        $response = $this->get('brands');
        $response->assertSee('Brand 1');
    }

    /** @test */
    public function it_creates_a_new_brand()
    {
        $response = $this->post("brands", [
            'name' => 'Brand 1'
        ]);

        $this->assertDatabaseHas('brands', [
            'name' => 'Brand 1',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating()
    {
        $response = $this->json('POST', "brands");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_brand()
    {
        $brand = factory(Brand::class)->create();

        $response = $this->patch("brands/{$brand->id}", [
            'name' => 'Brand 1'
        ]);

        $this->assertDatabaseHas('brands', [
            'name' => 'Brand 1',
            'id'   => $brand->id
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating()
    {
        $brand = factory(Brand::class)->create();

        $response = $this->json('PATCH', "brands/{$brand->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_brand()
    {
        $brand = factory(Brand::class)->create();

        $this->json('DELETE', "brands/{$brand->id}");
        $this->assertDatabaseMissing('brands', [
            'id' => $brand->id
        ]);
    }

}
