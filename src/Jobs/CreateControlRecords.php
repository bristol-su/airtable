<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Events\RowCreated;
use Illuminate\Support\Arr;

class CreateControlRecords extends CreateRecords
{

    public array $uniqueIdColumnName;

    public function __construct(array $data, string $apiKey, string $baseId, string $tableName, array $uniqueIdColumnName)
    {
        parent::__construct($data, $apiKey, $baseId, $tableName);
        $this->uniqueIdColumnName = $uniqueIdColumnName;
    }

    public function withResponse(array $response)
    {
        $airtableIdManager = app(AirtableIdManager::class);
        if(
            array_key_exists('id', $response) &&
            array_key_exists('fields', $response)) {
            RowCreated::dispatch(
                $airtableIdManager->getIdFromColumnNames($response['fields'], $this->uniqueIdColumnName),
                'control_' . $this->tableName . '_' . $this->baseId,
                $response['id'],
                $response['fields']
            );
        }
    }



}
