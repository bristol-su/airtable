<?php

namespace BristolSU\AirTable\Tests\Jobs;

use BristolSU\AirTable\AirTable;
use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\Tests\AirTable\TestCase;
use GuzzleHttp\Client;

class CreateRecordsTest extends TestCase
{

    /** @test */
    public function the_airtable_client_is_called(){
        $airtable = $this->prophesize(AirTable::class);
        $airtable->setApiKey('myApiKey1')->shouldBeCalled();
        $airtable->setBaseId('myBaseId1')->shouldBeCalled();
        $airtable->setTableName('myTableName1')->shouldBeCalled();
        $airtable->createRows([
            ['Field1' => 'Value1'],
            ['Field1' => 'Value2']
        ], true)->shouldBeCalled();
        $data = [
            ['Field1' => 'Value1'],
            ['Field1' => 'Value2'],
        ];
        $job = new CreateRecords($data, 'myApiKey1', 'myBaseId1', 'myTableName1');
        
        $job->handle($airtable->reveal());
    }
    
}