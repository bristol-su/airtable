<?php


namespace BristolSU\AirTable\Jobs;


use BristolSU\AirTable\AirTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Spatie\RateLimitedMiddleware\RateLimited;

class DeleteRows implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    private string $apiKey;
    private string $baseId;
    private string $tableName;
    private array $ids;
    private bool $debug = false;

    public function __construct(array $ids
        , string $apiKey, string $baseId, string $tableName)
    {
        $this->apiKey = $apiKey;
        $this->baseId = $baseId;
        $this->tableName = $tableName;
        $this->ids = $ids;
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
        $this->log('Deleting Rows');
        $airTable->deleteRows($this->ids);
        $this->log('Deleted Rows');
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