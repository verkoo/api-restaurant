<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Box;
use Verkoo\Common\Entities\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BoxUserTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function an_admin_can_add_users_to_a_box()
    {
        $box  = factory(Box::class)->create();
        $anotherUser = factory(User::class)->create();
        $this->actingAs($this->adminUser());

        $this->post("/boxes/{$box->id}/users", [
            'user_id' => $anotherUser->id
        ]);

        $this->assertDatabaseHas('box_user', [
            'box_id' => $box->id,
            'user_id' => $anotherUser->id
        ]);
    }

    /** @test */
    public function an_admin_can_delete_users_from_a_box()
    {
        $user = factory(User::class)->create();
        $box  = factory(Box::class)->create();
        $box->addUser($user);

        $this->actingAs($this->adminUser());

        $this->delete("/boxes/{$box->id}/users/{$user->id}");

        $this->assertDatabaseMissing('box_user', [
            'box_id' => $box->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function a_no_admin_user_cannot_add_users_to_a_box()
    {
        $user = factory(User::class)->make(['name' => 'User']);
        $box  = factory(Box::class)->create();
        $this->actingAs($user);

        $response = $this->json("POST", "/boxes/{$box->id}/users");
        $response->assertStatus(302);
    }

    /** @test */
    public function a_no_admin_user_cannot_delete_users_from_a_box()
    {
        $user = factory(User::class)->create(['name' => 'User']);
        $box  = factory(Box::class)->create();
        $box->addUser($user);

        $this->actingAs($user);

        $response = $this->json("DELETE", "/boxes/{$box->id}/users/{$user->id}");
        $response->assertStatus(302);
    }
}
