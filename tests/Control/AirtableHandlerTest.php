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
        
        $jobWasValid = function(CreateRecords $job) {
            return true;
        };
        
        Bus::assertDispatched(FlushRows::class, function($job) use ($jobWasValid) {
            return count($job->chained) === 1 && $jobWasValid(unserialize($job->chained[0]));
        });
        
    }
    
}