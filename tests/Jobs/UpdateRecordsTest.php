<?php

namespace BristolSU\AirTable\Tests\Jobs;

use BristolSU\AirTable\AirTable;
use BristolSU\AirTable\Jobs\UpdateRecords;
use BristolSU\Tests\AirTable\TestCase;

class UpdateRecordsTest extends TestCase
{

    /** @test */
    public function the_airtable_client_is_called(){
        $airtable = $this->prophesize(AirTable::class);
        $airtable->setApiKey('myApiKey1')->shouldBeCalled();
        $airtable->setBaseId('myBaseId1')->shouldBeCalled();
        $airtable->setTableName('myTableName1')->shouldBeCalled();
        $data = [
            ['id' => '123', 'fields' => ['Field1' => 'Value1']],
            ['id' => '456', 'fields' => ['Field1' => 'Value2']]
        ];
        $airtable->updateRows($data, true)->shouldBeCalled();
        $job = new UpdateRecords($data, 'myApiKey1', 'myBaseId1', 'myTableName1');

        $job->handle($airtable->reveal());

    }

}
