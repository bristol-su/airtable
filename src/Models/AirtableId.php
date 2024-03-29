<?php

namespace BristolSU\AirTable\Models;

use Database\Factories\AirtableIdFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirtableId extends Model
{
    use HasFactory;

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

    public function modelId(): string
    {
        return $this->model_id;
    }

    public function modelType(): string
    {
        return (string) $this->model_type;
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function newFactory()
    {
        return new AirtableIdFactory();
    }
}
