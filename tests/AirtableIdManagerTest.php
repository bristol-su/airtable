<?php

namespace BristolSU\Tests\AirTable;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Models\AirtableId;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AirtableIdManagerTest extends TestCase
{

    /** @test */
    public function hasModel_returns_true_if_a_model_matching_the_given_criteria_exists(){
        $airtableIdManager = new AirtableIdManager();

        $model = factory(AirtableId::class)->create([
            'airtable_id' => 'airtable123',
            'model_type' => 'progress_123',
            'model_id' => 11
        ]);

        $this->assertTrue($airtableIdManager->hasModel(11, 'progress_123'));
    }

    /** @test */
    public function hasModel_returns_false_if_a_model_matching_the_given_criteria_does_not_exist(){
        $airtableIdManager = new AirtableIdManager();

        $this->assertFalse($airtableIdManager->hasModel(11, 'progress_123'));
    }

    /** @test */
    public function getAirtableId_returns_the_airtable_id_if_the_model_exists(){
        $airtableIdManager = new AirtableIdManager();

        $model = factory(AirtableId::class)->create([
            'airtable_id' => 'airtable123',
            'model_type' => 'progress_123',
            'model_id' => 11
        ]);

        $this->assertEquals('airtable123', $airtableIdManager->getAirtableId(11, 'progress_123'));
    }

    /** @test */
    public function getAirtableId_throws_an_exception_if_the_model_does_not_exist(){
        $this->expectException(ModelNotFoundException::class);

        $airtableIdManager = new AirtableIdManager();

        $this->assertEquals('airtable123', $airtableIdManager->getAirtableId(11, 'progress_123'));
    }

    /** @test */
    public function saveRowId_creates_a_new_model_if_one_does_not_exist(){
        $airtableIdManager = new AirtableIdManager();
        $airtableIdManager->saveRowId(11, 'progress_123', 'airtable456');

        $this->assertDatabaseHas('airtable_ids', [
            'model_id' => 11,
            'model_type' => 'progress_123',
            'airtable_id' => 'airtable456'
        ]);
    }

    /** @test */
    public function saveRowId_throws_an_exception_if_a_model_exists(){
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The airtable record ID airtable456 is already in use.');

        $model = factory(AirtableId::class)->create([
            'airtable_id' => 'airtable456',
            'model_type' => 'progress_123',
            'model_id' => 11
        ]);

        $airtableIdManager = new AirtableIdManager();
        $airtableIdManager->saveRowId(11, 'progress_123', 'airtable456');
    }

}
