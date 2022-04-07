<?php

namespace BristolSU\AirTable;

use BristolSU\AirTable\Models\AirtableId;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AirtableIdManager
{

    public function hasModel(string $modelId, string $modelType): bool
    {
        return AirtableId::where('model_id', $modelId)
                ->where('model_type', $modelType)
                ->count() > 0;
    }

    public function getAirtableId(string $modelId, string $modelType): string
    {
        return AirtableId::where('model_id', $modelId)
            ->where('model_type', $modelType)
            ->latest()
            ->firstOrFail()
            ->airtableId();
    }

    public function saveRowId(string $modelId, string $modelType, string $airtableId): AirtableId
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

    public function getIdFromColumnNames(array $data, array $columnNames): string
    {
        return implode('--', array_map(function($name) use ($data) {
            if(array_key_exists($name, $data)) {
                return (is_array($data[$name]) ? json_encode($data[$name]) : $data[$name]);
            }
            return '';
        }, $columnNames));
    }

    public function hasReservedModel(string $modelId, string $modelType): bool
    {
        return cache()->has($this->getCacheKey($modelId, $modelType));
    }

    public function reserveModel(string $modelId, string $modelType): void
    {
        cache()->put($this->getCacheKey($modelId, $modelType), true, 900);
    }

    private function getCacheKey(string $modelId, string $modelType): string
    {
        return sprintf('AirtableIdManager_%s_%s', $modelId, $modelType);
    }

}
