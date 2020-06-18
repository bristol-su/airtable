<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class FlushRows implements ShouldQueue
{
    use Dispatchable, Queueable;

    private string $apiKey;

    private string $baseId;

    private string $tableName;

    private bool $debug = false;

    public function __construct(string $apiKey, string $baseId, string $tableName)
    {
        $this->apiKey = $apiKey;
        $this->baseId = $baseId;
        $this->tableName = $tableName;
    }

    public function handle(AirTable $airTable)
    {
        $airTable->setApiKey($this->apiKey);
        $airTable->setBaseId($this->baseId);
        $airTable->setTableName($this->tableName);
        $this->log('Retrieving IDs');
        $ids = $airTable->getIdsFromTable();
        $this->log('Retrieved IDs');
        $this->log('Deleting Rows');
        foreach(array_chunk($ids, 50) as $idsToDelete) {
            dispatch((new DeleteRows(
                $idsToDelete,
                $this->apiKey,
                $this->baseId,
                $this->tableName)
            )->withDebug($this->debug));
        }
    }

    public function withDebug(bool $debug)
    {
        $this->debug = $debug;
        return $this;
    }

    private function log(string $string)
    {
        if($this->debug) {
            Log::debug($string);
        }
    }
}