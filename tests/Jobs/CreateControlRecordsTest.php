<?php

namespace BristolSU\AirTable\Tests\Jobs;

use BristolSU\AirTable\AirTable;
use BristolSU\AirTable\Events\RowCreated;
use BristolSU\AirTable\Jobs\CreateControlRecords;
use BristolSU\Tests\AirTable\TestCase;
use Illuminate\Support\Facades\Event;
use Prophecy\Argument;

class CreateControlRecordsTest extends TestCase
{

    /** @test */
    public function it_dispatches_an_event_with_the_correct_fields(){

        Event::fake([RowCreated::class]);

        $job = new CreateControlRecords([
            ['fields' => ['Field1' => 'Value1', 'My Row ID' => 1]],
            ['fields' => ['Field1' => 'Value2', 'My Row ID' => 2]],
        ], 'myApiKey1', 'myBaseId1', 'myTableName1', ['My Row ID']);

        $job->withResponse([
            [
                'id' => 'airtable1',
                'fields' => [
                    'Field1' => 'Value1',
                    'My Row ID' => 1
                ]
            ],
            [
                'id' => 'airtable2',
                'fields' => [
                    'Field1' => 'Value2',
                    'My Row ID' => 2
                ]
            ],
        ]);

        Event::assertDispatched(RowCreated::class, function(RowCreated $event) {
            return $event->modelId === '1' &&
                $event->modelType === 'control_myTableName1_myBaseId1' &&
                $event->airtableRowId === 'airtable1' &&
                $event->fields === [
                    'Field1' => 'Value1',
                    'My Row ID' => 1
                ];
        });

        Event::assertdispatched(RowCreated::class, function(RowCreated $event) {
            return $event->modelId === '2' &&
                $event->modelType === 'control_myTableName1_myBaseId1' &&
                $event->airtableRowId === 'airtable2' &&
                $event->fields === [
                    'Field1' => 'Value2',
                    'My Row ID' => 2
                ];
        });

    }

}
