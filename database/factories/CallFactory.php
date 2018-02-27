<?php

use App\Models\Call;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(Call::class, function (Faker $faker) {
    return [
        'call_time' => '0000-00-00 00:00:00',
        'bpd_call_id' => $faker->postcode, //not really a postcode in real life
        'priority' => $faker->numberBetween(0, 3),
        'district' => "NE",
        'address' => $faker->streetAddress,
        'description' => $faker->text,
        'longitude' => 0,
        'latitude' => 0,
    ];
});

$factory->state(Call::class, 'high-priority', [
    'priority' => 3
]);


$factory->state(Call::class, 'low-priority', [
    'priority' => 1
]);

$factory->state(Call::class, 'district-NW', [
    'district' => 'NW'
]);

$factory->state(Call::class, 'district-SE', [
    'district' => 'SE'
]);

$factory->state(Call::class, 'today', [
    'call_time' => Carbon::today('America/New_York')->toDateTimeString(),
]);

$factory->state(Call::class, 'this-week', [
    'call_time' => (Carbon::today('America/New_York')->startOfWeek()->isSameDay(Carbon::today('America/New_York')) ? 
        Carbon::tomorrow('America/New_York')->toDateTimeString() : Carbon::yesterday('America/New_York')->toDateTimeString()),
]);

$factory->state(Call::class, 'this-month', [
    'call_time' => (Carbon::today('America/New_York')->startOfMonth()->isSameDay(Carbon::today('America/New_York')) ? 
        Carbon::tomorrow('America/New_York')->toDateTimeString() : Carbon::yesterday('America/New_York')->toDateTimeString()),
]);