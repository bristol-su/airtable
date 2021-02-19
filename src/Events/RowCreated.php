<?php

namespace BristolSU\AirTable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RowCreated
{
    use Dispatchable, SerializesModels;

    public $row;

    public function __construct($row)
    {
        $this->row = $row;
    }
}