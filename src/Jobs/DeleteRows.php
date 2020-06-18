<?php


namespace BristolSU\AirTable\Jobs;


use BristolSU\AirTable\AirTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\RateLimitedMiddleware\RateLimited;

class DeleteRows implements ShouldQueue
{
    use Dispatchable, Queueable;

    private string $apiKey;
    private string $baseId;
    private string $tableName;
    private array $ids;

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
        $airTable->deleteRows($this->ids);
    }

    public function retryUntil() :  \DateTime
    {
        return now()->addHours(5);
    }
}