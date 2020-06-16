<?php

namespace BristolSU\Tests\AirTable;

use BristolSU\Support\Testing\AssertsEloquentModels;
use BristolSU\Support\Testing\HandlesAuthentication;
use BristolSU\Support\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Prophecy\PhpUnit\ProphecyTrait;

class TestCase extends BaseTestCase
{
    use ProphecyTrait, AssertsEloquentModels, DatabaseMigrations, HandlesAuthentication;
    
}