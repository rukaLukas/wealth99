<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Coin::class, function (Faker $faker) {
    return [
        'coin_id' => $faker->unique()->word,
    ];
});

