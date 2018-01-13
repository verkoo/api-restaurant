<?php

use Tests\TestCase;
use Illuminate\Support\Facades\Gate;
use Verkoo\Common\RolePolicies;
use Verkoo\Common\Entities\Role;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_user_has_roles()
    {
        factory(Role::class)->create(['name' => 'admin']);
        $user = factory(User::class)->create();

        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }

    /** @test */
    public function a_rol_has_permissions()
    {
        $role = factory(Role::class)->create(['name' => 'admin']);
        $createPost = factory(Permission::class)->create(['name' => 'create_post']);
        $role->givePermissionTo($createPost);

        $this->assertCount(1, $role->permissions);
    }

    /** @test */
    public function a_user_cannot_use_not_allowed_resources()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create(['name' => 'editor']);
        $createPost = factory(Permission::class)->create(['name' => 'create_post']);
        factory(Permission::class)->create(['name' => 'manage_founds']);

        $role->givePermissionTo($createPost);
        $user->assignRole('editor');

        RolePolicies::define();

        $this->actingAs($user);

        $this->assertTrue(Gate::allows('create_post'));
        $this->assertFalse(Gate::allows('manage_founds'));
    }
}