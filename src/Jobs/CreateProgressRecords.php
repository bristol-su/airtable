<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\Events\RowCreated;

class CreateProgressRecords extends CreateRecords
{
    public function withResponse(array $response)
    {
        if(
            array_key_exists('id', $response) &&
            array_key_exists('fields', $response) &&
            array_key_exists('Activity Instance ID', $response['fields'])) {
            RowCreated::dispatch(
                (int) $response['fields']['Activity Instance ID'],
                'progress_' . $this->tableName,
                $response['id'],
                $response['fields']
            );
        }
    }

}
