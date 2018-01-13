<?php

namespace Tests\Feature;

use App\Entities\Zone;
use Tests\TestCase;
use App\Entities\Table;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TablesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_tables()
    {
        factory(Table::class)->create(['name' => 'Table 1']);

        $response = $this->get('tables');
        $response->assertSee('Table 1');
    }

    /** @test */
    public function it_creates_a_new_table()
    {
        $zone = factory(Zone::class)->create();

        $response = $this->post("tables", [
            'name' => 'Table 1',
            'zone_id' => $zone->id,
        ]);

        $this->assertDatabaseHas('tables', [
            'name' => 'Table 1',
            'zone_id' => $zone->id
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_table()
    {
        $response = $this->json('POST', "tables");
        $this->assertValidationErrors($response, [
            'name',
            'zone_id',
        ]);
    }

    /** @test */
    public function it_updates_a_table()
    {
        $table = factory(Table::class)->create();

        $zone = factory(Zone::class)->create();

        $response = $this->patch("tables/{$table->id}", [
            'name' => 'Table 1',
            'zone_id' => $zone->id,
        ]);

        $this->assertDatabaseHas('tables', [
            'name' => 'Table 1',
            'zone_id' => $zone->id,
            'id' => $table->id
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_table()
    {
        $table = factory(Table::class)->create();

        $response = $this->json('PATCH', "tables/{$table->id}");
        $this->assertValidationErrors($response, [
            'name',
            'zone_id',
        ]);
    }

    /** @test */
    public function it_deletes_a_brand()
    {
        $table = factory(Table::class)->create();

        $this->json('DELETE', "tables/{$table->id}");
        $this->assertDatabaseMissing('tables', [
            'id' => $table->id
        ]);
    }
}
