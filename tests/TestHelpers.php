<?php

namespace Tests;


use Verkoo\Common\Entities\Role;
use Verkoo\Common\Entities\User;

trait TestHelpers
{
    protected $adminUser;

    public function adminUser()
    {
        if ($this->adminUser) {
            return $this->adminUser;
        }

        $this->adminUser = factory(User::class)->create();
        factory(Role::class)->create(['name' => 'admin']);
        $this->adminUser->assignRole('admin');

        return $this->adminUser;
    }
}