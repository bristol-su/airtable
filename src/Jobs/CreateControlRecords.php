<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirTable;
use BristolSU\AirTable\Events\RowCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Spatie\RateLimitedMiddleware\RateLimited;

class CreateControlRecords extends CreateRecords
{

    protected string $uniqueIdRowName;

    public function __construct(array $data, string $apiKey, string $baseId, string $tableName, string $uniqueIdRowName)
    {
        parent::__construct($data, $apiKey, $baseId, $tableName);
        $this->uniqueIdRowName = $uniqueIdRowName;
    }

    public function withResponse(ResponseInterface $response)
    {
        $arrayResponse = json_decode($response->getBody()->getContents(), true);

        if(
            array_key_exists('id', $arrayResponse) &&
            array_key_exists('fields', $arrayResponse) &&
            array_key_exists($this->uniqueIdRowName, $arrayResponse['fields'])) {
            RowCreated::dispatch(
                'control_' . $this->tableName,
                $arrayResponse['fields'][$this->uniqueIdRowName],
                $arrayResponse['id'],
                $arrayResponse['fields']
            );
        }
    }



}