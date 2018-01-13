<?php


$factory->define(\Verkoo\Common\Entities\User::class, function (Faker\Generator $faker) {
    return [
        'name'           => $faker->name,
        'username'       => $faker->unique()->userName,
        'email'          => $faker->unique()->email,
        'password'       => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(\Verkoo\Common\Entities\User::class, function (Faker\Generator $faker) {
    return [
        'name'           => $faker->name,
        'username'       => $faker->unique()->userName,
        'email'          => $faker->unique()->email,
        'password'       => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(\Verkoo\Common\Entities\Options::class, function (Faker\Generator $faker) {
    return [
        'company_name'    => '',
        'address'         => '',
        'cif'             => '',
        'phone'           => '',
        'default_printer' => '',
        'print_ticket_when_cash' => 0,
        'open_drawer_when_cash' => 1,
        'tax_id'   => function () {
            return factory(\Verkoo\Common\Entities\Tax::class)->create()->id;
        },
        'cash_customer'   => function () {
            return factory(\Verkoo\Common\Entities\Customer::class)->create()->id;
        },
        'payment_id'   => function () {
            return factory(\Verkoo\Common\Entities\Payment::class)->create()->id;
        },
    ];
});

$factory->define(\Verkoo\Common\Entities\Role::class, function (Faker\Generator $faker) {
    return [
        'name'  => $faker->name,
        'label' => $faker->sentence
    ];
});

$factory->define(\Verkoo\Common\Entities\Permission::class, function (Faker\Generator $faker) {
    return [
        'name'  => $faker->name,
        'label' => $faker->sentence
    ];
});

$factory->define(\App\Entities\Product::class, function (Faker\Generator $faker) {

    return [
        'name'              => $faker->sentence(2),
        'category_id'       => function () {
            return factory(\Verkoo\Common\Entities\Category::class)->create()->id;
        },
        'price'             => $faker->randomFloat(2,0,3),
        'description'       => $faker->paragraph,
        'short_description' => 'short description',
        'photo'             => $faker->randomElement([null, "/img/{$faker->randomDigit}.jpg"]),
        'ean13'             => '12345678',
        'cost'              => 1,
        'active'            => 1,
        'stock'             => 100,
        'initial_stock'     => 0,
        'stock_control'     => 1,
        'condition'         => $faker->randomElement(['new', 'used', 'refurbished']),
        'brand_id'          => null,
        'supplier_id'       => null,
        'kitchen_id'        =>  null
    ];
});

$factory->define(\Verkoo\Common\Entities\Customer::class, function (Faker\Generator $faker) {
    return [
        'name'     => $faker->name,
        'dni'      => $faker->randomNumber(8). 'A',
        'phone'    => '6'. $faker->randomNumber(8),
        'email'    => $faker->email,
        'comments' => $faker->paragraph
    ];
});

$factory->define(\Verkoo\Common\Entities\Address::class, function (Faker\Generator $faker) {
    return [
        'customer_id'   => function(){
            return factory(\Verkoo\Common\Entities\Customer::class)->create()->id;
        },
        'city'        => $faker->city,
        'postcode'    => $faker->postcode,
        'province'    => $faker->randomElement(config('options.province_codes')),
        'address'     => $faker->address,
        'default'     => true
    ];
});

$factory->define(\App\Entities\Line::class, function (Faker\Generator $faker) {
    return [
        'product_id'   => function(){
            return factory(\App\Entities\Product::class)->create()->id;
        },
        'product_name' => $faker->sentence(3),
        'quantity'     => 1,
        'discount'     => '10',
        'price'        => '12,50',
        'cost'         => '8',
        'vat'          => 21,
        'kitchen_id'   => null,
        'ordered'      => 0
    ];
});

$factory->define(\Verkoo\Common\Entities\Category::class, function (Faker\Generator $faker) {
    return [
        'name'   => $faker->sentence(2),
        'active' => true,
        'parent' => null
    ];
});

$factory->define(\App\Entities\Menu::class, function (Faker\Generator $faker) {
    return [
        'name'   => $faker->sentence(2),
        'description' => $faker->paragraph,
        'active' => 1,
        'salad' => 1,
        'bread' => 1
    ];
});

$factory->define(\Verkoo\Common\Entities\Supplier::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(\App\Entities\MenuOrder::class, function (Faker\Generator $faker) {
    return [
        'order_id' => function(){
            return factory(\App\Entities\Order::class)->create()->id;
        },
        'menu_id' => function(){
            return factory(\App\Entities\Menu::class)->create()->id;
        },
        'name' => $faker->sentence,
        'price' => 0,
    ];
});

$factory->define(\App\Entities\Dish::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(\App\Entities\Kitchen::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'printer' => ''
    ];
});

$factory->define(\App\Entities\Extra::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'price' => 1250
    ];
});

$factory->define(\Verkoo\Common\Entities\Brand::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});

$factory->define(\Verkoo\Common\Entities\Expediture::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});

$factory->define(\Verkoo\Common\Entities\ExpeditureType::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});

$factory->define(\Verkoo\Common\Entities\UnitOfMeasure::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});

$factory->define(\App\Entities\Zone::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->domainWord,
    ];
});
$factory->define(\App\Entities\Allergen::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->domainWord,
    ];
});

$factory->define(\App\Entities\Table::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->domainWord,
        'zone_id' => function () {
            return factory(\App\Entities\Zone::class)->create()->id;
        }
    ];
});

$factory->define(\App\Entities\Quote::class, function (Faker\Generator $faker) {
    return [
        'date'        => $faker->date('d/m/Y'),
        'discount'    => '0',
        'customer_id' => factory(\Verkoo\Common\Entities\Customer::class)->create()->id
    ];
});

$factory->define(\Verkoo\Common\Entities\Quote::class, function (Faker\Generator $faker) {
    return [
        'date'        => $faker->date('d/m/Y'),
        'discount'    => '0',
        'customer_id' => factory(\Verkoo\Common\Entities\Customer::class)->create()->id
    ];
});

$factory->define(\Verkoo\Common\Entities\Tax::class, function (Faker\Generator $faker) {
    return [
        'name'        => $faker->name,
        'percentage' => $faker->randomNumber(2)
    ];
});

$factory->define(\App\Entities\Order::class, function (Faker\Generator $faker) {
    return [
        'date'        => $faker->date('d/m/Y'),
        'number'      => '',
        'serie'       => 1,
        'user_id'     => function (){
            return factory(\Verkoo\Common\Entities\User::class)->create()->id;
        },
        'session_id'     => function (){
            return factory(\Verkoo\Common\Entities\Session::class)->create()->id;
        },
        'customer_id' => function() {
            return factory(\Verkoo\Common\Entities\Customer::class)->create()->id;
        },
        'payment_id'      => null,
        'cashed_amount' => 0,
    ];
});

//$factory->define(\Verkoo\Common\Entities\Order::class, function (Faker\Generator $faker) {
//    return [
//        'date'        => $faker->date('d/m/Y'),
//        'number'      => '',
//        'serie'       => 1,
//        'user_id'     => function (){
//            return factory(\Verkoo\Common\Entities\User::class)->create()->id;
//        },
//        'session_id'     => function (){
//            return factory(\App\Entities\Session::class)->create()->id;
//        },
//        'customer_id' => function() {
//            return factory(\App\Entities\Customer::class)->create()->id;
//        },
//        'payment_id'      => null,
//        'cashed_amount' => 0,
//    ];
//});

$factory->define(\App\Entities\DeliveryNote::class, function (Faker\Generator $faker) {
    return [
        'date'        => $faker->date('d/m/Y'),
        'number'      => '',
        'serie'       => 1,
        'customer_id' => function () {
            return factory(\Verkoo\Common\Entities\Customer::class)->create()->id;
        }
    ];
});

$factory->define(\Verkoo\Common\Entities\DeliveryNote::class, function (Faker\Generator $faker) {
    return [
        'date'        => $faker->date('d/m/Y'),
        'number'      => '',
        'serie'       => 1,
        'customer_id' => function () {
            return factory(\Verkoo\Common\Entities\Customer::class)->create()->id;
        }
    ];
});

$factory->define(\Verkoo\Common\Entities\DefaultDeliveryNote::class, function (Faker\Generator $faker) {
    return [
        'customer_id' => function () {
            return factory(\Verkoo\Common\Entities\Customer::class)->create()->id;
        }
    ];
});

$factory->define(\Verkoo\Common\Entities\ExpeditureDeliveryNote::class, function (Faker\Generator $faker) {
    return [
        'date'        => $faker->date('d/m/Y'),
        'supplier_id' => function () {
            return factory(\Verkoo\Common\Entities\Supplier::class)->create()->id;
        }
    ];
});

$factory->define(\Verkoo\Common\Entities\Invoice::class, function (Faker\Generator $faker) {
    return [
        'date'        => $faker->date('d/m/Y'),
        'customer_id' => function(){
            return factory(\Verkoo\Common\Entities\Customer::class)->create()->id;
        }
    ];
});

$factory->define(\Verkoo\Common\Entities\Box::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'description'   => $faker->paragraph(),
    ];
});

$factory->define(\Verkoo\Common\Entities\Payment::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(\App\Entities\Combination::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(\App\Entities\DishMenu::class, function (Faker\Generator $faker) {
    return [
        'dish_id' => function(){
            return factory(\App\Entities\Dish::class)->create()->id;
        },
        'menu_id' => function(){
            return factory(\App\Entities\Menu::class)->create()->id;
        },
    ];
});

$factory->define(\Verkoo\Common\Entities\Session::class, function () {
    return [
        'initial_cash' => 20,
        'final_cash'   => 40,
        'open'         => 1,
        'box_id' => function(){
            return factory(\Verkoo\Common\Entities\Box::class)->create()->id;
        }
    ];
});

