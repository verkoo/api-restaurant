<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiUserTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function it_gets_the_id_from_the_current_user_in_the_system()
    {
        $user = factory(\Verkoo\Common\Entities\User::class)->create();
        $this->actingAs($user, "api");

        $response = $this->get('/api/users');
        $response->assertStatus(200)
            ->assertJSON($user->toArray());
    }
}
