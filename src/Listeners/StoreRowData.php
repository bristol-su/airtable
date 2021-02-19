<?php

namespace BristolSU\AirTable\Listeners;

use BristolSU\AirTable\Events\RowCreated;
use BristolSU\AirTable\Models\AirtableId;
use Illuminate\Support\Facades\Log;

class StoreRowData {

    protected $row;

    public function __construct()
    {

    }

    public function handle(RowCreated $event)
    {
        $this->row = json_decode($event->row);

        foreach($this->row->records as $row) {
            AirtableId::create([
                'airtable_id' => $row->id,
                'activity_instance_id' => $row->fields->{'Activity Instance ID'}
            ]);
        }
    }
}