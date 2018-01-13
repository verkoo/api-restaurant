<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Role;
use Tests\TestCase;
use Verkoo\Common\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UsersTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function an_admin_can_list_all_the_users()
    {
        factory(User::class)->create(['name' => 'Usuario']);

        $this->actingAs($this->adminUser());

        $response = $this->get('users');
        $response->assertSee('Usuario');
    }

    /** @test */
    public function a_no_admin_user_cannot_list_all_the_users()
    {
        $user = factory(User::class)->create(['name' => 'Usuario no admin']);

        $this->actingAs($user);

        $response = $this->get('users');
        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_form_when_creating_a_user()
    {
        $this->actingAs($this->adminUser());

        $response = $this->json('POST', "users");

        $this->assertValidationErrors($response, [
            'name',
            'role',
            'username',
            'password',
        ]);
    }

    /** @test */
    public function email_must_be_valid()
    {
        $this->actingAs($this->adminUser());

        $response = $this->json('POST', "users", [
            'email' => 'WRONG-EMAIL'
        ]);

        $this->assertValidationErrors($response, 'email');
    }

    /** @test */
    public function password_must_be_confirmed()
    {
        $this->actingAs($this->adminUser());

        $response = $this->json('POST', "users", [
            'password' => '123456',
            'password_confirmation' => '1234567',
        ]);

        $this->assertValidationErrors($response, 'password');
    }

    /** @test */
    public function an_admin_creates_a_new_user()
    {
        $role = factory(Role::class)->create(["name" => "waiter"]);
        $this->actingAs($this->adminUser());

        $response = $this->post("users", [
            'name'  => 'John Doe',
            'username'  => 'john',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $role->name,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'john'
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function a_no_admin_user_cannot_see_the_register_form()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $response = $this->get("/users/create");
        $response->assertStatus(403);
    }

    /** @test */
    public function a_no_admin_user_cannot_create_a_user()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $response = $this->post("/users");
        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_form_when_updating_a_user()
    {
        $this->actingAs($this->adminUser());
        $user = factory(User::class)->create();

        $response = $this->json('PATCH', "users/{$user->id}", [
            'password' => '123456',
            'password_confirmation' => '1234567',
            'email' => 'WRONG-EMAIL',
        ]);

        $this->assertValidationErrors($response, [
            'password',
            'email',
            'name',
            'role',
            'username',
        ]);
    }

    /** @test */
    public function an_admin_updates_a_user()
    {
        $user = factory(User::class)->create();

        $role = factory(Role::class)->create(["name" => "waiter"]);
        $this->actingAs($this->adminUser());

        $response = $this->patch("users/{$user->id}", [
            'name'  => 'John Doe',
            'username'  => 'john',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $role->name,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'john',
            'id' => $user->id

        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function an_admin_can_delete_a_user()
    {
        $this->actingAs($this->adminUser());

        $user = factory(User::class)->create();

        $this->delete("users/{$user->id}");
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    /** @test */
    public function a_no_admin_user_cannot_delete_another_user()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $this->actingAs($user);

        $response = $this->delete("/users/{$user2->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function a_no_admin_user_cannot_delete_himself()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $response = $this->delete("/users/{$user->id}");
        $response->assertStatus(403);
    }
}
