<?php

namespace BristolSU\AirTable\Control;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Jobs\CreateControlRecords;
use BristolSU\AirTable\Jobs\UpdateRecords;
use BristolSU\ControlDB\Export\FormattedItem;
use BristolSU\ControlDB\Export\Handler\Handler;
use Carbon\Carbon;
use Illuminate\Support\Arr;
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
        $this->log(sprintf('Processing %u items to save to AirTable', count($items)));

        $toCreate = [];
        $toUpdate = [];

        /** @var AirtableIdManager $airtableIdManager */
        $airtableIdManager = app(AirtableIdManager::class);
        $itemType = 'control_' . $this->config('tableName') . '_' . $this->config('baseId');

        foreach($items as $item) {
            $itemId = $airtableIdManager->getIdFromColumnNames(
                $item->toArray(),
                Arr::wrap($this->config('uniqueIdColumnName'))
            );

            if($itemId === null || $itemId === '') {
                throw new \Exception('Please ensure the `uniqueIdColumnName` gives a unique ID for every record.');
            }
            if($airtableIdManager->hasModel($itemId, $itemType)) {
                $this->log(sprintf('Set model #%u (%s) to be updated', $itemId, $itemType));
                $toUpdate[] = [
                    'id' => $airtableIdManager->getAirtableId($itemId, $itemType),
                    'fields' => $item->toArray()
                ];
            } else {
                $this->log(sprintf('Set model #%u (%s) to be created', $itemId, $itemType));
                $toCreate[] = [
                    'fields' => $item->toArray()
                ];
            }
        }
        foreach(array_chunk($toCreate, 10) as $data) {
            dispatch((new CreateControlRecords(
                $data,
                $this->config('apiKey'),
                $this->config('baseId'),
                $this->config('tableName'),
                Arr::wrap($this->config('uniqueIdColumnName'))
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
