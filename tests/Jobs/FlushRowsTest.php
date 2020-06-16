<?php

namespace BristolSU\AirTable\Tests\Jobs;

use BristolSU\AirTable\AirTable;
use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\AirTable\Jobs\FlushRows;
use BristolSU\Tests\AirTable\TestCase;
use GuzzleHttp\Client;

class FlushRowsTest extends TestCase
{

    /** @test */
    public function the_airtable_client_is_called(){
        $airtable = $this->prophesize(AirTable::class);
        $airtable->setApiKey('myApiKey1')->shouldBeCalled();
        $airtable->setBaseId('myBaseId1')->shouldBeCalled();
        $airtable->setTableName('myTableName1')->shouldBeCalled();
        $airtable->flushTable()->shouldBeCalled();
        $job = new FlushRows('myApiKey1', 'myBaseId1', 'myTableName1');
        
        $job->handle($airtable->reveal());
    }
    
}