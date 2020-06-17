<?php

namespace BristolSU\Tests\AirTable\Control;

use BristolSU\AirTable\Control\AirtableHandler;
use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\AirTable\Jobs\FlushRows;
use BristolSU\Tests\AirTable\TestCase;
use Illuminate\Support\Facades\Bus;

class AirtableHandlerTest extends TestCase
{

    /** @test */
    public function it_fires_a_flush_rows_and_a_create_rows_job(){
        Bus::fake([FlushRows::class, CreateRecords::class]);
        
        $handler = new AirtableHandler([
            'apiKey' => 'myApiKey',
            'baseId' => 'myBaseId',
            'tableName' => 'myTableName'
        ]);
        $handler->export([$this->newRole()]);
        
        Bus::assertDispatched(CreateRecords::class);
        
        Bus::assertDispatched(FlushRows::class);
        
    }
    
}