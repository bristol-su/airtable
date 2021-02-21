<?php

namespace BristolSU\AirTable;

use BristolSU\AirTable\Models\AirtableId;

class AirtableIdManager
{

    public function hasModel(int $modelId, string $modelType): bool
    {
        return AirtableId::where('model_id', $modelId)
                ->where('model_type', $modelType)
                ->count() > 0;
    }

    public function getAirtableId(int $modelId, string $modelType): string
    {
        return AirtableId::where('model_id', $modelId)
            ->where('model_type', $modelType)
            ->firstOrFail()
            ->airtableId();
    }

    public function saveRowId(int $modelId, string $modelType, string $airtableId): AirtableId
    {
        return AirtableId::create([
            'model_id' => $modelId,
            'model_type' => $modelType,
            'airtable_id' => $airtableId
        ]);
    }

}