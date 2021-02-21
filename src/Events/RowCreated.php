<?php

namespace BristolSU\AirTable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RowCreated
{
    use Dispatchable, SerializesModels;

    public int $modelId;

    public string $modelType;

    public string $airtableRowId;

    public array $fields;

    public function __construct(int $modelId, string $modelType, string $airtableRowId, array $fields)
    {
        $this->airtableRowId = $airtableRowId;
        $this->fields = $fields;
        $this->modelId = $modelId;
        $this->modelType = $modelType;
    }

}