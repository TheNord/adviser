<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Region::class, function (Faker $faker) {
    return [
        'name' => $name = $faker->unique()->city,
        'slug' => str_slug($name),
        'parent_id' => null,
    ];
});
