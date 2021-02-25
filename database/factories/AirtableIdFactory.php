<?php

$factory->define(\BristolSU\AirTable\Models\AirtableId::class, function(\Faker\Generator $faker) {
    return [
        'airtable_id' => sprintf('rec%s', \Illuminate\Support\Str::random('10')),
        'model_type' => 'progress_tbl1234567890_app0987654321',
        'model_id' => 2,
    ];
});
