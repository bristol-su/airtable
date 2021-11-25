<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Events\RowCreated;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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
        foreach($this->data as $record) {
            $airtableRecord = array_shift($response);
            if(array_key_exists('id', $airtableRecord)) {
                RowCreated::dispatch(
                    $airtableIdManager->getIdFromColumnNames($record['fields'], $this->uniqueIdColumnName),
                    'control_' . $this->tableName . '_' . $this->baseId,
                    $airtableRecord['id'],
                    $record['fields']
                );
            }
        }
    }

    protected function log(string $string)
    {
        if($this->debug) {
            Log::debug($string);
        }
    }


}
