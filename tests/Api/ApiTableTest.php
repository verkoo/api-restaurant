<?php

use App\Entities\Table;
use App\Entities\Zone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiTableTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_tables_from_a_given_zone()
    {
        $zone  = factory(Zone::class)->create();

        $tables = factory(Table::class)->times(3)->make();
        $zone->tables()->saveMany($tables);

        $response = $this->get("api/zones/{$zone->id}");
        $response->assertStatus(200)
            ->assertJson($tables->toArray());
    }
}
