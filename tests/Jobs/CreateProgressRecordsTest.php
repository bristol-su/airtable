<?php

namespace BristolSU\AirTable\Tests\Jobs;

use BristolSU\AirTable\AirTable;
use BristolSU\AirTable\Events\RowCreated;
use BristolSU\AirTable\Jobs\CreateProgressRecords;
use BristolSU\Tests\AirTable\TestCase;
use Illuminate\Support\Facades\Event;

class CreateProgressRecordsTest extends TestCase
{

    /** @test */
    public function it_dispatches_an_event_with_the_correct_fields(){
        Event::fake([RowCreated::class]);

        $job = new CreateProgressRecords([
            ['Field1' => 'Value1'],
            ['Field1' => 'Value2'],
        ], 'myApiKey1', 'myBaseId1', 'myTableName1');

        $job->withResponse([
            'id' => 'airtable1',
            'fields' => [
                'Activity Instance ID' => 11,
                'Other' => 'Something'
            ]
        ]);

        Event::assertdispatched(RowCreated::class, function(RowCreated $event) {
            $this->assertEquals(11, $event->modelId);
            $this->assertEquals('progress_myTableName1_myBaseId1', $event->modelType);
            $this->assertEquals('airtable1', $event->airtableRowId);
            $this->assertEquals([
                'Activity Instance ID' => 11,
                'Other' => 'Something'
            ], $event->fields);
            return true;
        });

    }

}
