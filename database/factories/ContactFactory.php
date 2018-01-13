<?php

use Verkoo\Common\Entities\Contact;

$factory->define(Contact::class, function (Faker\Generator $faker) {
    return [
        'name'  => $faker->name,
        'phone' => $faker->phoneNumber,
        'phone2' => $faker->phoneNumber,
        'email' => $faker->email,
    ];
});
