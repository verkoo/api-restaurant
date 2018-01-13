<?php

use Verkoo\Common\Entities\Role;
use Verkoo\Common\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class UsersTest extends \Tests\BrowserKitTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_shows_the_user_create_form_when_click_link_in_list()
    {
        $this->actingAs($this->adminUser());

        $this->visit('/users')
            ->click('Nuevo Usuario')
            ->seePageIs("/users/create");
    }

    /** @test */
    public function it_shows_the_update_form_when_click_link_in_row()
    {
        $user = factory(User::class)->create(['name' => 'a']);

        $this->actingAs($this->adminUser());

        $this->visit('/users')
            ->click('Editar')
            ->seePageIs("users/{$user->id}/edit")
            ->seeInField('name', $user->name);
    }

    /** @test */
    public function a_user_with_waiter_role_only_can_see_the_pos_screen()
    {
        $user = factory(User::class)->create();
        factory(Role::class)->create(['name' => 'waiter']);
        $user->assignRole('waiter');

        $this->actingAs($user);

        $this->visit("/products")
            ->seePageIs("/tpv");

        $this->visit("/categories")
            ->seePageIs("/tpv");
    }
}