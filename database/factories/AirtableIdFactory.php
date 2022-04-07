<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AirtableIdFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \BristolSU\AirTable\Models\AirtableId::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'airtable_id' => sprintf('rec%s', \Illuminate\Support\Str::random('10')),
            'model_type' => sprintf('progress_tbl%s_app%s', $this->faker->numberBetween(1111111111, 9999999999), $this->faker->numberBetween(1111111111, 9999999999)),
            'model_id' => 2,
        ];
    }
}
