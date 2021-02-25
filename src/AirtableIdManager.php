<?php

namespace BristolSU\AirTable;

use BristolSU\AirTable\Models\AirtableId;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        try {
            AirtableId::findOrFail($airtableId);
            throw new \Exception(
                sprintf('The airtable record ID %s is already in use.', $airtableId),
                422
            );
        } catch (ModelNotFoundException $e) {
            return AirtableId::create([
                'model_id' => $modelId,
                'model_type' => $modelType,
                'airtable_id' => $airtableId
            ]);
        }
    }

}
