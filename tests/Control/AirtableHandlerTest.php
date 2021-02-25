<?php

namespace BristolSU\Tests\AirTable\Control;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Control\AirtableHandler;
use BristolSU\AirTable\Jobs\CreateControlRecords;
use BristolSU\AirTable\Jobs\UpdateRecords;
use BristolSU\ControlDB\Export\FormattedItem;
use BristolSU\Tests\AirTable\TestCase;
use Illuminate\Support\Facades\Bus;
use Prophecy\Argument;

class AirtableHandlerTest extends TestCase
{

    /** @test */
    public function it_only_creates_rows_if_none_yet_created_on_airtable(){
        Bus::fake([CreateControlRecords::class, UpdateRecords::class]);

        $item1 = $this->newItem([
            'My Row ID' => 1,
            'Another Column' => 'abc',
            'Yet Another Column' => 'def'
        ]);
        $item2 = $this->newItem([
            'My Row ID' => 2,
            'Another Column' => 'ghi',
            'Yet Another Column' => 'jkl'
        ]);
        $item3 =$this->newItem([
            'My Row ID' => 3,
            'Another Column' => 'mno',
            'Yet Another Column' => 'pqr'
        ]);

        $handler = new AirtableHandler([
            'apiKey' => 'myApiKey',
            'baseId' => 'myBaseId',
            'tableName' => 'myTableName',
            'uniqueIdColumnName' => 'My Row ID'
        ]);

        $reflector = new \ReflectionObject($handler);
        $saveMethod = $reflector->getMethod('save');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($handler, [$item1, $item2, $item3]);

        Bus::assertDispatched(CreateControlRecords::class, function(CreateControlRecords $job) {
            $this->assertEquals([
                ['fields' => ['My Row ID' => 1, 'Another Column' => 'abc', 'Yet Another Column' => 'def']],
                ['fields' => ['My Row ID' => 2, 'Another Column' => 'ghi', 'Yet Another Column' => 'jkl']],
                ['fields' => ['My Row ID' => 3, 'Another Column' => 'mno', 'Yet Another Column' => 'pqr']]
            ], $job->data);
            $this->assertEquals('myApiKey', $job->apiKey);
            $this->assertEquals('myBaseId', $job->baseId);
            $this->assertEquals('myTableName', $job->tableName);
            return true;
        });
    }

    /** @test */
    public function it_only_updates_rows_if_all_rows_already_exist(){
        Bus::fake([CreateControlRecords::class, UpdateRecords::class]);

        $item1 = $this->newItem([
            'My Row ID' => 1,
            'Another Column' => 'abc',
            'Yet Another Column' => 'def'
        ]);
        $item2 = $this->newItem([
            'My Row ID' => 2,
            'Another Column' => 'ghi',
            'Yet Another Column' => 'jkl'
        ]);
        $item3 =$this->newItem([
            'My Row ID' => 3,
            'Another Column' => 'mno',
            'Yet Another Column' => 'pqr'
        ]);

        $airtableIdManager = $this->prophesize(AirtableIdManager::class);
        $airtableIdManager->hasModel(1, 'control_myTableName_myBaseId')->willReturn(true);
        $airtableIdManager->hasModel(2, 'control_myTableName_myBaseId')->willReturn(true);
        $airtableIdManager->hasModel(3, 'control_myTableName_myBaseId')->willReturn(true);
        $airtableIdManager->getAirtableId(1, 'control_myTableName_myBaseId')->willReturn('airtable1');
        $airtableIdManager->getAirtableId(2, 'control_myTableName_myBaseId')->willReturn('airtable2');
        $airtableIdManager->getAirtableId(3, 'control_myTableName_myBaseId')->willReturn('airtable3');
        $this->instance(AirtableIdManager::class, $airtableIdManager->reveal());

        $handler = new AirtableHandler([
            'apiKey' => 'myApiKey',
            'baseId' => 'myBaseId',
            'tableName' => 'myTableName',
            'uniqueIdColumnName' => 'My Row ID'
        ]);

        $reflector = new \ReflectionObject($handler);
        $saveMethod = $reflector->getMethod('save');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($handler, [$item1, $item2, $item3]);

        Bus::assertDispatched(UpdateRecords::class, function(UpdateRecords $job) {
            $this->assertEquals([
                ['id' => 'airtable1', 'fields' => ['My Row ID' => 1, 'Another Column' => 'abc', 'Yet Another Column' => 'def']],
                ['id' => 'airtable2', 'fields' => ['My Row ID' => 2, 'Another Column' => 'ghi', 'Yet Another Column' => 'jkl']],
                ['id' => 'airtable3', 'fields' => ['My Row ID' => 3, 'Another Column' => 'mno', 'Yet Another Column' => 'pqr']]
            ], $job->data);
            $this->assertEquals('myApiKey', $job->apiKey);
            $this->assertEquals('myBaseId', $job->baseId);
            $this->assertEquals('myTableName', $job->tableName);
            return true;
        });

    }

    /** @test */
    public function it_can_mix_updating_and_creating_records(){
        Bus::fake([CreateControlRecords::class, UpdateRecords::class]);

        $item1 = $this->newItem([
            'My Row ID' => 1,
            'Another Column' => 'abc',
            'Yet Another Column' => 'def'
        ]);
        $item2 = $this->newItem([
            'My Row ID' => 2,
            'Another Column' => 'ghi',
            'Yet Another Column' => 'jkl'
        ]);
        $item3 =$this->newItem([
            'My Row ID' => 3,
            'Another Column' => 'mno',
            'Yet Another Column' => 'pqr'
        ]);

        $airtableIdManager = $this->prophesize(AirtableIdManager::class);
        $airtableIdManager->hasModel(1, 'control_myTableName_myBaseId')->willReturn(true);
        $airtableIdManager->hasModel(2, 'control_myTableName_myBaseId')->willReturn(false);
        $airtableIdManager->hasModel(3, 'control_myTableName_myBaseId')->willReturn(true);
        $airtableIdManager->getAirtableId(1, 'control_myTableName_myBaseId')->willReturn('airtable1');
        $airtableIdManager->getAirtableId(2, 'control_myTableName_myBaseId')->shouldNotBeCalled();
        $airtableIdManager->getAirtableId(3, 'control_myTableName_myBaseId')->willReturn('airtable3');
        $this->instance(AirtableIdManager::class, $airtableIdManager->reveal());

        $handler = new AirtableHandler([
            'apiKey' => 'myApiKey',
            'baseId' => 'myBaseId',
            'tableName' => 'myTableName',
            'uniqueIdColumnName' => 'My Row ID'
        ]);

        $reflector = new \ReflectionObject($handler);
        $saveMethod = $reflector->getMethod('save');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($handler, [$item1, $item2, $item3]);

        Bus::assertDispatched(UpdateRecords::class, function(UpdateRecords $job) {
            $this->assertEquals([
                ['id' => 'airtable1', 'fields' => ['My Row ID' => 1, 'Another Column' => 'abc', 'Yet Another Column' => 'def']],
                ['id' => 'airtable3', 'fields' => ['My Row ID' => 3, 'Another Column' => 'mno', 'Yet Another Column' => 'pqr']]
            ], $job->data);
            return true;
        });

        Bus::assertDispatched(CreateControlRecords::class, function(CreateControlRecords $job) {
            $this->assertEquals([
                ['fields' => ['My Row ID' => 2, 'Another Column' => 'ghi', 'Yet Another Column' => 'jkl']]
            ], $job->data);
            return true;
        });

    }

    /** @test */
    public function it_chunks_the_job_dispatch_to_rows_of_10(){
        Bus::fake([CreateControlRecords::class, UpdateRecords::class]);

        $createItems = [];
        $updateItems = [];

        for($i = 0; $i < 39; $i++) {
            $createItems[] = $this->newItem([
                'My Row ID' => $i,
                'Another Column' => 'col-' . $i,
                'Yet Another Column' => 'col2-' . $i
            ]);
        }
        for($i = 39; $i < 74; $i++) {
            $updateItems[] = $this->newItem([
                'My Row ID' => $i,
                'Another Column' => 'col-' . $i,
                'Yet Another Column' => 'col2-' . $i
            ]);
        }

        $airtableIdManager = $this->prophesize(AirtableIdManager::class);
        foreach($updateItems as $item) {
            $airtableIdManager->hasModel($item->getItem('My Row ID'), 'control_myTableName_myBaseId')->willReturn(true);
            $airtableIdManager->getAirtableId($item->getItem('My Row ID'), 'control_myTableName_myBaseId')->willReturn('airtable' . $item->getItem('My Row ID'));
        }
        foreach($createItems as $item) {
            $airtableIdManager->hasModel($item->getItem('My Row ID'), 'control_myTableName_myBaseId')->willReturn(false);
        }
        $this->instance(AirtableIdManager::class, $airtableIdManager->reveal());

        $handler = new AirtableHandler([
            'apiKey' => 'myApiKey',
            'baseId' => 'myBaseId',
            'tableName' => 'myTableName',
            'uniqueIdColumnName' => 'My Row ID'
        ]);

        $reflector = new \ReflectionObject($handler);
        $saveMethod = $reflector->getMethod('save');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($handler, array_merge($createItems, $updateItems));

        Bus::assertDispatchedTimes(UpdateRecords::class, 4);
        Bus::assertDispatchedTimes(CreateControlRecords::class, 4);
    }

    private function newItem(array $attributes)
    {
        $item = $this->prophesize(FormattedItem::class);
        foreach($attributes as $key => $value) {
            $item->getItem($key, Argument::any())->willReturn($value);
        }
        $item->toArray()->willReturn($attributes);

        return $item->reveal();
    }

}
