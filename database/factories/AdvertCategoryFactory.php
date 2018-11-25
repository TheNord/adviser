<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Adverts\Category::class, function (Faker $faker) {
    return [
        'name' => $name = $faker->unique()->name,
        'slug' => str_slug($name),
        'parent_id' => null,
    ];
});
