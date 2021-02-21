<?php

namespace BristolSU\AirTable\Control;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Jobs\CreateControlRecords;
use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\AirTable\Jobs\FlushRows;
use BristolSU\AirTable\Jobs\UpdateRecords;
use BristolSU\ControlDB\Export\FormattedItem;
use BristolSU\ControlDB\Export\Handler\Handler;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AirtableHandler extends Handler
{

    /**
     * Save each item to AirTable
     * 
     * @param FormattedItem[] $items
     * @return mixed|void
     */
    protected function save(array $items)
    {
        $toCreate = [];
        $toUpdate = [];

        /** @var AirtableIdManager $airtableIdManager */
        $airtableIdManager = app(AirtableIdManager::class);
        $itemType = 'control_' . $this->config('tableName');

        foreach($items as $item) {
            $itemId = $item->getItem($this->config('uniqueIdRowName'));
            if($airtableIdManager->hasModel($itemId, $itemType)) {
                $toUpdate[] = [
                    'id' => $airtableIdManager->getAirtableId($itemId, $itemType),
                    'fields' => $item->toArray()
                ];
            } else {
                $toCreate[] = ['fields' => $item->toArray()];
            }
        }

        foreach(array_chunk($toCreate, 10) as $data) {
            dispatch((new CreateControlRecords(
                $data,
                $this->config('apiKey'),
                $this->config('baseId'),
                $this->config('tableName'),
                $this->config('uniqueIdRowName')
            ))->withDebug($this->config('debug', false)));
        }

        foreach(array_chunk($toUpdate, 10) as $data) {
            dispatch((new UpdateRecords(
                $data,
                $this->config('apiKey'),
                $this->config('baseId'),
                $this->config('tableName')
            ))->withDebug($this->config('debug', false)));
        }

        $this->log('Created Records');

    }

    private function log(string $string)
    {
        if($this->config('debug', false)) {
            Log::debug($string);
        }
    }
}
