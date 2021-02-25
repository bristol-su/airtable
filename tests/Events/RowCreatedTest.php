<?php

namespace BristolSU\Tests\AirTable\Events;

use BristolSU\AirTable\Events\RowCreated;
use BristolSU\Tests\AirTable\TestCase;
use Illuminate\Support\Facades\Event;

class RowCreatedTest extends TestCase
{

    /** @test */
    public function it_can_be_dispatched(){
        Event::fake([RowCreated::class]);

        RowCreated::dispatch(1, 'progress_tbl1', 'airtable1', ['Row 1' => 'Value 1', 'Row 2' => 'Value 2']);

        Event::assertDispatched(RowCreated::class, function(RowCreated $event) {
            $this->assertEquals(1, $event->modelId);
            $this->assertEquals('progress_tbl1', $event->modelType);
            $this->assertEquals('airtable1', $event->airtableRowId);
            $this->assertEquals(['Row 1' => 'Value 1', 'Row 2' => 'Value 2'], $event->fields);
            return true;
        });
    }

}
