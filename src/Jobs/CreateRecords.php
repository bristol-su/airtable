<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Spatie\RateLimitedMiddleware\RateLimited;

abstract class CreateRecords implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    protected array $data;
    protected string $apiKey;
    protected string $baseId;
    protected string $tableName;
    protected bool $debug = false;

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
        $this->log('Creating Rows');
        $airTable->createRows($this->data, true, [$this, 'withResponse']);
        $this->log('Created Rows');
    }

    abstract public function withResponse(ResponseInterface $response);

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