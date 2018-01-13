<?php

use Tests\TestCase;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiSupplierTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_customers()
    {
        create(Supplier::class,[
            'name' => 'John Doe'
        ]);
        $response = $this->get("api/suppliers");
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'John Doe'
        ]);
    }
}