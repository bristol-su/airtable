<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Spatie\RateLimitedMiddleware\RateLimited;

class UpdateRecords implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    private array $data;
    private string $apiKey;
    private string $baseId;
    private string $tableName;
    private bool $debug = false;

    public function __construct(array $data, string $apiKey, string $baseId, string $tableName)
    {
        $this->data = $data;
        $this->apiKey = $apiKey;
        $this->baseId = $baseId;
        $this->tableName = $tableName;
    }

    public function middleware()
    {
        $rateLimitedMiddleware = (new RateLimited())
            ->key('airtable')
            ->allow(1)
            ->everySeconds(1)
            ->releaseAfterSeconds(3);

        return [$rateLimitedMiddleware];
    }
    
    public function handle(AirTable $airTable)
    {
        $airTable->setApiKey($this->apiKey);
        $airTable->setBaseId($this->baseId);
        $airTable->setTableName($this->tableName);
        $this->log('Updating Rows');
        $airTable->updateRows($this->data, true);
        $this->log('Updated Rows');
    }

    public function withDebug(bool $debug)
    {
        $this->debug = $debug;
        return $this;
    }

    public function retryUntil() :  \DateTime
    {
        return now()->addHours(5);
    }

    private function log(string $string)
    {
        if($this->debug) {
            Log::debug($string);
        }
    }

}