<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Events\RowCreated;

class CreateProgressRecords extends CreateRecords
{
    public function withResponse(array $response)
    {
        foreach($this->data as $record) {
            $airtableRecord = array_shift($response);
            if(array_key_exists('id', $airtableRecord)) {
                RowCreated::dispatch(
                    $record['fields']['Activity Instance ID'],
                    'progress_' . $this->tableName . '_' . $this->baseId,
                    $airtableRecord['id'],
                    $record['fields']
                );
            }
        }
    }

}
