<?php

namespace BristolSU\AirTable\Listeners;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Events\RowCreated;

class StoreNewRowData
{

    /**
     * @var AirtableIdManager
     */
    private AirtableIdManager $airtableIdManager;

    public function __construct(AirtableIdManager $airtableIdManager)
    {
        $this->airtableIdManager = $airtableIdManager;
    }

    public function handle(RowCreated $event)
    {
        $this->airtableIdManager->saveRowId(
            $event->modelId,
            $event->modelType,
            $event->airtableRowId
        );
    }
}
