<?php

namespace BristolSU\Tests\AirTable\Listeners;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Events\RowCreated;
use BristolSU\AirTable\Listeners\StoreNewRowData;
use BristolSU\Tests\AirTable\TestCase;

class StoreNewRowDataTest extends TestCase
{

    /** @test */
    public function it_saves_a_new_row_id(){
        $airtableIdManager = $this->prophesize(AirtableIdManager::class);
        $airtableIdManager->saveRowId(22, 'progress_tbl1', 'AirtableRowId')->shouldBeCalled();

        $event = new RowCreated(22, 'progress_tbl1', 'AirtableRowId', ['field' => 'one']);

        $listener = new StoreNewRowData($airtableIdManager->reveal());
        $listener->handle($event);
    }

}
