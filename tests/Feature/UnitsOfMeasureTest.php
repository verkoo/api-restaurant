<?php

namespace Tests\Feature;

use Tests\TestCase;
use Verkoo\Common\Entities\UnitOfMeasure;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UnitsOfMeasureTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_units_of_measure()
    {
        factory(UnitOfMeasure::class)->create(['name' => 'Kilos']);

        $response = $this->get('units-of-measure');
        $response->assertSee('Kilos');
    }

    /** @test */
    public function it_creates_a_new_unit_of_measure()
    {
        $response = $this->post("units-of-measure", [
            'name' => 'Kilos'
        ]);

        $this->assertDatabaseHas('units_of_measure', [
            'name' => 'Kilos',
        ]);

        $response->assertRedirect('units-of-measure');
    }

    /** @test */
    public function it_validates_form_when_creating_units_of_measure()
    {
        $response = $this->json('POST', "units-of-measure");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_unit_of_measure()
    {
        $unitOfMeasure = factory(UnitOfMeasure::class)->create();

        $response = $this->patch("units-of-measure/{$unitOfMeasure->id}", [
            'name' => 'Kilos'
        ]);

        $this->assertDatabaseHas('units_of_measure', [
            'name' => 'Kilos',
            'id'   => $unitOfMeasure->id
        ]);

        $response->assertRedirect('units-of-measure');
    }

    /** @test */
    public function it_validates_form_when_updating_units_of_measure()
    {
        $unitOfMeasure = factory(UnitOfMeasure::class)->create();

        $response = $this->json('PATCH', "units-of-measure/{$unitOfMeasure->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_unit_of_measure()
    {
        $unitOfMeasure = factory(UnitOfMeasure::class)->create();

        $this->json('DELETE', "units-of-measure/{$unitOfMeasure->id}");
        $this->assertDatabaseMissing('units_of_measure', [
            'id' => $unitOfMeasure->id
        ]);
    }
}
