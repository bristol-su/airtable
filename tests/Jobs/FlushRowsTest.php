<?php

namespace BristolSU\AirTable\Tests\Jobs;

use BristolSU\AirTable\AirTable;
use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\AirTable\Jobs\DeleteRows;
use BristolSU\AirTable\Jobs\FlushRows;
use BristolSU\Tests\AirTable\TestCase;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Bus;

class FlushRowsTest extends TestCase
{

    /** @test */
    public function the_airtable_client_is_called(){
        Bus::fake(DeleteRows::class);
        $airtable = $this->prophesize(AirTable::class);
        $airtable->setApiKey('myApiKey1')->shouldBeCalled();
        $airtable->setBaseId('myBaseId1')->shouldBeCalled();
        $airtable->setTableName('myTableName1')->shouldBeCalled();
        $airtable->getIdsFromTable()->shouldBeCalled()->willReturn(
            array_fill(0, 100, 'rec123')
        );
        $job = new FlushRows('myApiKey1', 'myBaseId1', 'myTableName1');
        $job->handle($airtable->reveal());
        
        Bus::assertDispatchedTimes(DeleteRows::class, 2);
    }
    
}