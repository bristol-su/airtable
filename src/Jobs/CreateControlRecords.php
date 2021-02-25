<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\Events\RowCreated;

class CreateControlRecords extends CreateRecords
{

    public string $uniqueIdRowName;

    public function __construct(array $data, string $apiKey, string $baseId, string $tableName, string $uniqueIdRowName)
    {
        parent::__construct($data, $apiKey, $baseId, $tableName);
        $this->uniqueIdRowName = $uniqueIdRowName;
    }

    public function withResponse(array $response)
    {
        if(
            array_key_exists('id', $response) &&
            array_key_exists('fields', $response) &&
            array_key_exists($this->uniqueIdRowName, $response['fields'])) {
            RowCreated::dispatch(
                (int) $response['fields'][$this->uniqueIdRowName],
                'control_' . $this->tableName,
                $response['id'],
                $response['fields']
            );
        }
    }



}
