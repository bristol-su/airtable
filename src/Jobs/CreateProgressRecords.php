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
            if(
                array_key_exists('id', $airtableRecord) &&
                array_key_exists('fields', $airtableRecord) &&
                array_key_exists('Activity Instance ID', $record)) {
                RowCreated::dispatch(
                    $record['Activity Instance ID'],
                    'progress_' . $this->tableName . '_' . $this->baseId,
                    $airtableRecord['id'],
                    $record
                );
            }
        }
    }

}
