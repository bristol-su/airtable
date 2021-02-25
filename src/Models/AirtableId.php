<?php

namespace BristolSU\AirTable\Models;

use Illuminate\Database\Eloquent\Model;

class AirtableId extends Model
{
    protected $table = 'airtable_ids';

    protected $primaryKey = 'airtable_id';

    public $incrementing = false;

    protected $fillable = [
        'airtable_id',
        'model_type',
        'model_id',
    ];

    public function airtableId(): string
    {
        return (string) $this->airtable_id;
    }

    public function modelId(): int
    {
        return (int) $this->model_id;
    }

    public function modelType(): string
    {
        return (string) $this->model_type;
    }
}
