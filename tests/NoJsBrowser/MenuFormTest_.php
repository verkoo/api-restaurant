<?php

use App\Entities\User;
use App\Entities\Role;
use App\Entities\Category;
use App\Entities\Permission;
use App\Services\RolePolicies;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @group backend
 */
class MenuFormTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    public function setUp()
    {
        parent::setUp();
        $this->admin = factory(User::class)->create();

        $role = factory(Role::class)->create(['name' => 'admin']);
        $updateSettings = factory(Permission::class)->create(['name' => 'update_settings']);
        $role->givePermissionTo($updateSettings);
        $this->admin->assignRole('admin');
    }

    /** @test */
    public function an_admin_can_see_the_menu_page()
    {
        $category = factory(Category::class)->create();
        factory(\App\Entities\Product::class)->create([
            'category_id' => $category->id,
            'name'        => 'Test Product'
        ]);

        $this->actingAs($this->admin);

        RolePolicies::define();
        
        $this->visit("/menu")
            ->seePageIs("/menu")
            ->see('Test Product');
    }

    /** @test */
    public function a_no_admin_user_cannot_see_the_menu_page()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $this->visit("/menu")
            ->seePageIs("/tpv");
    }
}