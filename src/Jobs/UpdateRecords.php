<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;

class UpdateRecords implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    public array $data;
    public string $apiKey;
    public string $baseId;
    public string $tableName;
    public bool $debug = false;

    public function __construct(array $data, string $apiKey, string $baseId, string $tableName)
    {
        $this->data = $data;
        $this->apiKey = $apiKey;
        $this->baseId = $baseId;
        $this->tableName = $tableName;
        $this->onQueue(sprintf('airtable_%s', config('app.env')));
    }

    public function middleware()
    {
        return [
            (new RateLimited('airtable'))
        ];
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

    protected function log(string $string)
    {
        if($this->debug) {
            Log::debug($string);
        }
    }

}
