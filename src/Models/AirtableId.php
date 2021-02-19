<?php

namespace BristolSU\AirTable\Models;

use Illuminate\Database\Eloquent\Model;

class AirtableId extends Model
{
    protected $table = 'airtable_ids';

    protected $fillable = ['airtable_id', 'activity_instance_id'];
}