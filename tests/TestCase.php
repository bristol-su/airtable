<?php

namespace BristolSU\Tests\AirTable;

use BristolSU\AirTable\AirTableServiceProvider;
use BristolSU\Support\Testing\AssertsEloquentModels;
use BristolSU\Support\Testing\HandlesAuthentication;
use BristolSU\Support\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Prophecy\PhpUnit\ProphecyTrait;

class TestCase extends BaseTestCase
{
    use ProphecyTrait, AssertsEloquentModels, DatabaseMigrations, HandlesAuthentication;

    public function setUp(): void
    {
        parent::setUp();
        $this->withFactories(__DIR__.'/../database/factories');
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            AirTableServiceProvider::class
        ]);
    }
}
