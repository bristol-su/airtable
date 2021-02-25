<?php

$factory->define(\BristolSU\AirTable\Models\AirtableId::class, function(\Faker\Generator $faker) {
    return [
        'airtable_id' => sprintf('rec%s', \Illuminate\Support\Str::random('10')),
        'model_type' => sprintf('progress_tbl%s_app%s', $faker->numberBetween(1111111111, 9999999999), $faker->numberBetween(1111111111, 9999999999)),
        'model_id' => 2,
    ];
});
