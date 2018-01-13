<?php

use Illuminate\Database\Seeder;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Payment;
use Verkoo\Common\Entities\Permission;
use Verkoo\Common\Entities\Role;
use Verkoo\Common\Entities\User;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer = factory(Customer::class)->create([
            'name'     => 'Cash Customer',
            'dni'      => '',
            'phone'    => '',
            'email'    => '',
            'comments' => ''
        ]);

        $payment = factory(Payment::class)->create(["name" => 'Cash']);

        factory(Options::class)->create([
            'cash_customer' => $customer->id,
            'payment_id' => $payment->id
        ]);

        $role = factory(Role::class)->create([
            'name' => 'admin',
            'label' => 'Administrator'
        ]);

        factory(Role::class)->create([
            'name' => 'waiter',
            'label' => 'Camarero'
        ]);

        $createUsers = factory(Permission::class)->create([
            'name' => 'create_users',
            'label' => 'Create users in the system'
        ]);

        $changeSettings = factory(Permission::class)->create([
            'name' => 'update_settings',
            'label' => 'Update settings in the system'
        ]);

        $role->givePermissionTo($createUsers);
        $role->givePermissionTo($changeSettings);

        $user = factory(User::class)->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => bcrypt('admin')
        ]);

        $user->assignRole('admin');
    }
}
