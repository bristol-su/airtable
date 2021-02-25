<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\Events\RowCreated;

class CreateControlRecords extends CreateRecords
{

    public string $uniqueIdColumnName;

    public function __construct(array $data, string $apiKey, string $baseId, string $tableName, string $uniqueIdColumnName)
    {
        parent::__construct($data, $apiKey, $baseId, $tableName);
        $this->uniqueIdColumnName = $uniqueIdColumnName;
    }

    public function withResponse(array $response)
    {
        if(
            array_key_exists('id', $response) &&
            array_key_exists('fields', $response) &&
            array_key_exists($this->uniqueIdColumnName, $response['fields'])) {
            RowCreated::dispatch(
                (int) $response['fields'][$this->uniqueIdColumnName],
                'control_' . $this->tableName . '_' . $this->baseId,
                $response['id'],
                $response['fields']
            );
        }
    }



}
