<?php

namespace BristolSU\AirTable\Models;

use Illuminate\Database\Eloquent\Model;

class AirtableId extends Model
{
    protected $table = 'airtable_ids';

    protected $fillable = ['airtable_id', 'activity_instance_id'];

    function hasActivityInstance(int $activityInstanceId): bool
    {
        return $this->where('activity_instance_id', '=', $activityInstanceId)->count() > 0;
    }

    function getRowId(int $activityInstanceId): string
    {
        return $this->where('activity_instance_id', '=', $activityInstanceId)->pluck('airtable_id')->first();
    }
}