<?php

namespace BristolSU\AirTable\Jobs;

use BristolSU\AirTable\AirTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FlushRows implements ShouldQueue
{
    use Dispatchable, Queueable;

    private string $apiKey;
    private string $baseId;
    private string $tableName;

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
        $ids = $airTable->getIdsFromTable();
        foreach(array_chunk($ids, 50) as $idsToDelete) {
            dispatch(new DeleteRows(
                $idsToDelete,
                $this->apiKey,
                $this->baseId,
                $this->tableName)
            );
        }
    }

}