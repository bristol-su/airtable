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

class CreateProgressRecords extends CreateRecords
{
    public function withResponse(ResponseInterface $response)
    {
        $arrayResponse = json_decode($response->getBody()->getContents(), true);

        if(
            array_key_exists('id', $arrayResponse) &&
            array_key_exists('fields', $arrayResponse) &&
            array_key_exists('Activity Instance ID', $arrayResponse['fields'])) {
            RowCreated::dispatch(
                'progress',
                $arrayResponse['fields']['Activity Instance ID'],
                $arrayResponse['id'],
                $arrayResponse['fields']
            );
        }
    }

}