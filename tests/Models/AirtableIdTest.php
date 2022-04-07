<?php

namespace BristolSU\Tests\AirTable\Models;

use BristolSU\AirTable\Models\AirtableId;
use BristolSU\Tests\AirTable\TestCase;

class AirtableIdTest extends TestCase
{

    /** @test */
    public function it_can_be_created(){
        $model = AirtableId::factory()->create([
            'airtable_id' => 'airtable1',
            'model_type' => 'model1',
            'model_id' => 5
        ]);

        $this->assertTrue($model->exists);

        $this->assertDatabaseHas('airtable_ids', [
            'airtable_id' => 'airtable1',
            'model_type' => 'model1',
            'model_id' => 5
        ]);
    }

    /** @test */
    public function the_properties_can_be_retrieved_from_the_model(){
        /** @var AirtableId $model */
        $model = AirtableId::factory()->create([
            'airtable_id' => 'airtable1',
            'model_type' => 'model1',
            'model_id' => 5
        ]);

        $this->assertEquals('airtable1', $model->airtableId());
        $this->assertEquals('model1', $model->modelType());
        $this->assertEquals(5, $model->modelId());
    }

}
