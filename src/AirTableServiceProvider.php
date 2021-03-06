<?php

namespace BristolSU\AirTable;

use BristolSU\AirTable\Control\AirtableHandler as ControlAirTableHandler;
use BristolSU\AirTable\Events\RowCreated;
use BristolSU\AirTable\Listeners\StoreNewRowData;
use BristolSU\AirTable\Progress\AirtableHandler as ProgressAirTableHandler;
use BristolSU\ControlDB\Export\Exporter;
use BristolSU\ControlDB\Export\ExportManager;
use BristolSU\Support\Progress\ProgressExport;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AirTableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        ProgressExport::extend('airtable', function($container, $config) {
            $missingKey = null;
            if(!array_key_exists('apiKey', $config)) {
                $missingKey = 'apiKey';
            }
            if(!array_key_exists('tableName', $config)) {
                $missingKey = 'tableName';
            }
            if(!array_key_exists('baseId', $config)) {
                $missingKey = 'baseId';
            }
            if($missingKey !== null) {
                throw new \Exception(sprintf('The [%s] field must be given', $missingKey));
            }

            return new ProgressAirTableHandler(
                $config['baseId'],
                $config['tableName'],
                $config['apiKey'],
                (array_key_exists('debug', $config) ? (bool) $config['debug'] : false)
            );
        });

        Exporter::extend('airtable', function($container, $config) {
            $missingKey = null;
            if(!array_key_exists('apiKey', $config)) {
                $missingKey = 'apiKey';
            }
            if(!array_key_exists('tableName', $config)) {
                $missingKey = 'tableName';
            }
            if(!array_key_exists('baseId', $config)) {
                $missingKey = 'baseId';
            }
            if(!array_key_exists('uniqueIdColumnName', $config)) {
                $missingKey = 'uniqueIdColumnName';
            }
            if($missingKey !== null) {
                throw new \Exception(sprintf('The [%s] field must be given', $missingKey));
            }

            return new ControlAirTableHandler($config);
        });

        Event::listen(RowCreated::class, StoreNewRowData::class);
    }



}
