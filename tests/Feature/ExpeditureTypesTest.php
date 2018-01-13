<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\ExpeditureType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExpeditureTypesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_expediture_types()
    {
        factory(ExpeditureType::class)->create(['name' => 'Electricity']);

        $response = $this->get('expediture-types');
        $response->assertSee('Electricity');
    }

    /** @test */
    public function it_creates_a_new_expediture_type()
    {
        $parent = create(ExpeditureType::class);
        $response = $this->post("expediture-types", [
            'name' => 'Electricity',
            'parent' => $parent->id,
        ]);

        $this->assertDatabaseHas('expediture_types', [
            'name' => 'Electricity',
            'parent' => $parent->id,
        ]);

        $response->assertRedirect('expediture-types');
    }

    /** @test */
    public function it_validates_form_when_creating_a_expediture_type()
    {
        $response = $this->json('POST', "expediture-types");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function parent_can_be_null_creating_an_expediture()
    {
        $response = $this->json('POST', "expediture-types", [
            'name' => 'Electricity',
            'parent' => null
        ]);

        $this->assertDatabaseHas('expediture_types', [
            'name' => 'Electricity',
            'parent' => null,
        ]);

        $response->assertRedirect('expediture-types');
    }

    /** @test */
    public function parent_can_be_empty_string_creating_an_expediture()
    {
        $response = $this->json('POST', "expediture-types", [
            'name' => 'Electricity',
            'parent' => ''
        ]);

        $this->assertDatabaseHas('expediture_types', [
            'name' => 'Electricity',
            'parent' => null,
        ]);

        $response->assertRedirect('expediture-types');
    }

    /** @test */
    public function parent_must_be_valid_when_creating_an_expediture()
    {
        $response = $this->json('POST', "expediture-types", [
            'parent' => 9999
        ]);
        $this->assertValidationErrors($response, 'parent');
    }

    /** @test */
    public function it_updates_a_expediture_type()
    {
        $expeditureType = factory(ExpeditureType::class)->create();

        $response = $this->patch("expediture-types/{$expeditureType->id}", [
            'name' => 'Electricity'
        ]);

        $this->assertDatabaseHas('expediture_types', [
            'name' => 'Electricity',
            'id'   => $expeditureType->id
        ]);

        $response->assertRedirect('expediture-types');
    }

    /** @test */
    public function it_validates_form_when_updating_a_expediture_type()
    {
        $expeditureType = factory(ExpeditureType::class)->create();

        $response = $this->json('PATCH', "expediture-types/{$expeditureType->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_expediture_type()
    {
        $expeditureType = factory(ExpeditureType::class)->create();

        $this->json('DELETE', "expediture-types/{$expeditureType->id}");
        $this->assertDatabaseMissing('expediture_types', [
            'id' => $expeditureType->id
        ]);
    }

}
