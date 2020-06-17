<?php

namespace BristolSU\AirTable\Control;

use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\AirTable\Jobs\FlushRows;
use BristolSU\ControlDB\Export\FormattedItem;
use BristolSU\ControlDB\Export\Handler\Handler;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
        $creating = [];
        
        foreach($items as $item) {
            $creating[] = $item->toArray();
        }

        dispatch_now(
            new FlushRows(
                $this->config('apiKey'),
                $this->config('baseId'),
                $this->config('tableName')
            )
        );

        foreach(array_chunk($creating, 10) as $data) {
            dispatch(new CreateRecords(
                $data,
                $this->config('apiKey'),
                $this->config('baseId'),
                $this->config('tableName')
            ));
        }
    }
}
