<?php

namespace BristolSU\Tests\AirTable;

use BristolSU\AirTable\AirTable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class AirTableTest extends TestCase
{

    /** @test */
    public function the_api_key_can_be_set_and_got(){
        $airTable = new AirTable(
            $this->prophesize(Client::class)->reveal()
        );
        
        $airTable->setApiKey('1234');
        $this->assertEquals('1234', $airTable->getApiKey());

        $airTable->setApiKey('abcd');
        $this->assertEquals('abcd', $airTable->getApiKey());
    }

    /** @test */
    public function the_baseId_can_be_set_and_got(){
        $airTable = new AirTable(
            $this->prophesize(Client::class)->reveal()
        );

        $airTable->setBaseId('1234');
        $this->assertEquals('1234', $airTable->getBaseId());

        $airTable->setBaseId('abcd');
        $this->assertEquals('abcd', $airTable->getBaseId());
    }

    /** @test */
    public function the_tableName_can_be_set_and_got(){
        $airTable = new AirTable(
            $this->prophesize(Client::class)->reveal()
        );

        $airTable->setTableName('1234');
        $this->assertEquals('1234', $airTable->getTableName());

        $airTable->setTableName('abcd');
        $this->assertEquals('abcd', $airTable->getTableName());
    }
    
    /** @test */
    public function createRows_creates_up_to_10_rows(){
        $client = $this->prophesize(Client::class);
        $client->request('post', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'json' => [
                'records' => [
                    ['fields' => ['Field 1' => 'val1', 'Field 2' => 'val2']],
                    ['fields' => ['Field 1' => 'val3', 'Field 2' => 'val4']]
                ],
                'typecast' => false
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec123', 'fields' => ['Field 1' => 'val1', 'Field 2' => 'val2']],
                ['id' => 'rec456', 'fields' => ['Field 1' => 'val3', 'Field 2' => 'val4']],
            ]
        ])));
        
        $airtable = new AirTable($client->reveal());
        $airtable->setApiKey('apiKey123');
        $airtable->setBaseId('myBase');
        $airtable->setTableName('myTable');
        
        $airtable->createRows([
            [
                'Field 1' => 'val1',
                'Field 2' => 'val2'
            ],
            [
                'Field 1' => 'val3',
                'Field 2' => 'val4'
            ],
        ], false);
    }

    /** @test */
    public function deleteRows_deletes_up_to_10_rows(){
        $client = $this->prophesize(Client::class);
        $client->request('delete', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'records' => [
                    'rec1', 'rec2', 'rec3', 'rec4'
                ]
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], ''));

        $airtable = new AirTable($client->reveal());
        $airtable->setApiKey('apiKey123');
        $airtable->setBaseId('myBase');
        $airtable->setTableName('myTable');

        $airtable->deleteRows([
            'rec1', 'rec2', 'rec3', 'rec4'
        ]);
    }

    /** @test */
    public function createRows_creates_over_10_rows_with_throttling(){
        $client = $this->prophesize(Client::class);
        $client->request('post', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'json' => [
                'records' => [
                    ['fields' => ['Field 1' => 'val1', 'Field 2' => 'val10']],
                    ['fields' => ['Field 1' => 'val2', 'Field 2' => 'val9']],
                    ['fields' => ['Field 1' => 'val3', 'Field 2' => 'val8']],
                    ['fields' => ['Field 1' => 'val4', 'Field 2' => 'val7']],
                    ['fields' => ['Field 1' => 'val5', 'Field 2' => 'val6']],
                    ['fields' => ['Field 1' => 'val6', 'Field 2' => 'val5']],
                    ['fields' => ['Field 1' => 'val7', 'Field 2' => 'val4']],
                    ['fields' => ['Field 1' => 'val8', 'Field 2' => 'val3']],
                    ['fields' => ['Field 1' => 'val9', 'Field 2' => 'val2']],
                    ['fields' => ['Field 1' => 'val10', 'Field 2' => 'val1']],
                ],
                'typecast' => false
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec1', 'fields' => ['Field 1' => 'val1', 'Field 2' => 'val10']],
                ['id' => 'rec2', 'fields' => ['Field 1' => 'val2', 'Field 2' => 'val9']],
                ['id' => 'rec3', 'fields' => ['Field 1' => 'val3', 'Field 2' => 'val8']],
                ['id' => 'rec4', 'fields' => ['Field 1' => 'val4', 'Field 2' => 'val7']],
                ['id' => 'rec5', 'fields' => ['Field 1' => 'val5', 'Field 2' => 'val6']],
                ['id' => 'rec6', 'fields' => ['Field 1' => 'val6', 'Field 2' => 'val5']],
                ['id' => 'rec7', 'fields' => ['Field 1' => 'val7', 'Field 2' => 'val4']],
                ['id' => 'rec8', 'fields' => ['Field 1' => 'val8', 'Field 2' => 'val3']],
                ['id' => 'rec9', 'fields' => ['Field 1' => 'val9', 'Field 2' => 'val2']],
                ['id' => 'rec10', 'fields' => ['Field 1' => 'val10', 'Field 2' => 'val1']],
            ]
        ])));
        $client->request('post', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'json' => [
                'records' => [
                    ['fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
                    ['fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
                    ['fields' => ['Field 1' => 'val13', 'Field 2' => 'val11']],
                ],
                'typecast' => false
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec11', 'fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
                ['id' => 'rec12', 'fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
                ['id' => 'rec13', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
            ]
        ])));

        $airtable = new AirTable($client->reveal());
        $airtable->setApiKey('apiKey123');
        $airtable->setBaseId('myBase');
        $airtable->setTableName('myTable');

        $startTime = microtime(true);
        $airtable->createRows([
            ['Field 1' => 'val1', 'Field 2' => 'val10'],
            ['Field 1' => 'val2', 'Field 2' => 'val9'],
            ['Field 1' => 'val3', 'Field 2' => 'val8'],
            ['Field 1' => 'val4', 'Field 2' => 'val7'],
            ['Field 1' => 'val5', 'Field 2' => 'val6'],
            ['Field 1' => 'val6', 'Field 2' => 'val5'],
            ['Field 1' => 'val7', 'Field 2' => 'val4'],
            ['Field 1' => 'val8', 'Field 2' => 'val3'],
            ['Field 1' => 'val9', 'Field 2' => 'val2'],
            ['Field 1' => 'val10', 'Field 2' => 'val1'],
            ['Field 1' => 'val11', 'Field 2' => 'val13'],
            ['Field 1' => 'val12', 'Field 2' => 'val14'],
            ['Field 1' => 'val13', 'Field 2' => 'val11'],
        ], false);
        $executionTime = microtime(true) - $startTime;
        $this->assertGreaterThan(1, $executionTime);
    }

    /** @test */
    public function the_script_is_delayed_if_the_rate_limit_is_hit(){
        $client = $this->prophesize(Client::class);
        $client->request('post', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'json' => [
                'records' => [
                    ['fields' => ['Field 1' => 'val1', 'Field 2' => 'val10']],
                    ['fields' => ['Field 1' => 'val2', 'Field 2' => 'val9']],
                    ['fields' => ['Field 1' => 'val3', 'Field 2' => 'val8']],
                    ['fields' => ['Field 1' => 'val4', 'Field 2' => 'val7']],
                    ['fields' => ['Field 1' => 'val5', 'Field 2' => 'val6']],
                    ['fields' => ['Field 1' => 'val6', 'Field 2' => 'val5']],
                    ['fields' => ['Field 1' => 'val7', 'Field 2' => 'val4']],
                    ['fields' => ['Field 1' => 'val8', 'Field 2' => 'val3']],
                    ['fields' => ['Field 1' => 'val9', 'Field 2' => 'val2']],
                    ['fields' => ['Field 1' => 'val10', 'Field 2' => 'val1']],
                ],
                'typecast' => false
            ]
        ])->shouldBeCalled()->will(function($args, $mock) {
            $mock->request('post', 'https://api.airtable.com/v0/myBase/myTable', [
                'headers' => [
                    'Authorization' => 'Bearer apiKey123'
                ],
                'json' => [
                    'records' => [
                        ['fields' => ['Field 1' => 'val1', 'Field 2' => 'val10']],
                        ['fields' => ['Field 1' => 'val2', 'Field 2' => 'val9']],
                        ['fields' => ['Field 1' => 'val3', 'Field 2' => 'val8']],
                        ['fields' => ['Field 1' => 'val4', 'Field 2' => 'val7']],
                        ['fields' => ['Field 1' => 'val5', 'Field 2' => 'val6']],
                        ['fields' => ['Field 1' => 'val6', 'Field 2' => 'val5']],
                        ['fields' => ['Field 1' => 'val7', 'Field 2' => 'val4']],
                        ['fields' => ['Field 1' => 'val8', 'Field 2' => 'val3']],
                        ['fields' => ['Field 1' => 'val9', 'Field 2' => 'val2']],
                        ['fields' => ['Field 1' => 'val10', 'Field 2' => 'val1']],
                    ],
                    'typecast' => false
                ]
            ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
                'records' => [
                    ['id' => 'rec1', 'fields' => ['Field 1' => 'val1', 'Field 2' => 'val10']],
                    ['id' => 'rec2', 'fields' => ['Field 1' => 'val2', 'Field 2' => 'val9']],
                    ['id' => 'rec3', 'fields' => ['Field 1' => 'val3', 'Field 2' => 'val8']],
                    ['id' => 'rec4', 'fields' => ['Field 1' => 'val4', 'Field 2' => 'val7']],
                    ['id' => 'rec5', 'fields' => ['Field 1' => 'val5', 'Field 2' => 'val6']],
                    ['id' => 'rec6', 'fields' => ['Field 1' => 'val6', 'Field 2' => 'val5']],
                    ['id' => 'rec7', 'fields' => ['Field 1' => 'val7', 'Field 2' => 'val4']],
                    ['id' => 'rec8', 'fields' => ['Field 1' => 'val8', 'Field 2' => 'val3']],
                    ['id' => 'rec9', 'fields' => ['Field 1' => 'val9', 'Field 2' => 'val2']],
                    ['id' => 'rec10', 'fields' => ['Field 1' => 'val10', 'Field 2' => 'val1']],
                ]
            ])));
            throw new ClientException('Rate limited', new Request('get', ''), new Response(429, [], ''));
        });
        $client->request('post', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'json' => [
                'records' => [
                    ['fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
                    ['fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
                    ['fields' => ['Field 1' => 'val13', 'Field 2' => 'val11']],
                ],
                'typecast' => false
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec11', 'fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
                ['id' => 'rec12', 'fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
                ['id' => 'rec13', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
            ]
        ])));

        $airtable = new AirTable($client->reveal());
        $airtable::$rateLimitCooldown = 3;
        $airtable->setApiKey('apiKey123');
        $airtable->setBaseId('myBase');
        $airtable->setTableName('myTable');

        $startTime = microtime(true);
        $airtable->createRows([
            ['Field 1' => 'val1', 'Field 2' => 'val10'],
            ['Field 1' => 'val2', 'Field 2' => 'val9'],
            ['Field 1' => 'val3', 'Field 2' => 'val8'],
            ['Field 1' => 'val4', 'Field 2' => 'val7'],
            ['Field 1' => 'val5', 'Field 2' => 'val6'],
            ['Field 1' => 'val6', 'Field 2' => 'val5'],
            ['Field 1' => 'val7', 'Field 2' => 'val4'],
            ['Field 1' => 'val8', 'Field 2' => 'val3'],
            ['Field 1' => 'val9', 'Field 2' => 'val2'],
            ['Field 1' => 'val10', 'Field 2' => 'val1'],
            ['Field 1' => 'val11', 'Field 2' => 'val13'],
            ['Field 1' => 'val12', 'Field 2' => 'val14'],
            ['Field 1' => 'val13', 'Field 2' => 'val11'],
        ], false);
        $executionTime = microtime(true) - $startTime;
        $this->assertGreaterThan(4, $executionTime);
    }


    /** @test */
    public function retrieveRecords_gets_all_records_from_the_table(){
        $client = $this->prophesize(Client::class);
        $client->request('get', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'offset' => null
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec11', 'fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
                ['id' => 'rec12', 'fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
                ['id' => 'rec13', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
            ],
            'offset' => 'abc123'
        ])));
        $client->request('get', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'offset' => 'abc123'
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec14', 'fields' => ['Field 1' => 'val111', 'Field 2' => 'val131']],
                ['id' => 'rec15', 'fields' => ['Field 1' => 'val121', 'Field 2' => 'val141']],
                ['id' => 'rec16', 'fields' => ['Field 13' => 'val101', 'Field 2' => 'val111']],
            ]
        ])));
        
        $airtable = new AirTable($client->reveal());
        $airtable->setApiKey('apiKey123');
        $airtable->setBaseId('myBase');
        $airtable->setTableName('myTable');

        $response = $airtable->retrieveRecords();
        $this->assertEquals([
            ['id' => 'rec11', 'fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
            ['id' => 'rec12', 'fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
            ['id' => 'rec13', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
            ['id' => 'rec14', 'fields' => ['Field 1' => 'val111', 'Field 2' => 'val131']],
            ['id' => 'rec15', 'fields' => ['Field 1' => 'val121', 'Field 2' => 'val141']],
            ['id' => 'rec16', 'fields' => ['Field 13' => 'val101', 'Field 2' => 'val111']],
        ], $response);
    }
    
    /** @test */
    public function getIdsFromTable_gets_all_IDs_from_the_table(){
        $client = $this->prophesize(Client::class);
        $client->request('get', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'offset' => null
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec11', 'fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
                ['id' => 'rec12', 'fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
                ['id' => 'rec13', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
            ],
            'offset' => 'abc123'
        ])));
        $client->request('get', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'offset' => 'abc123'
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec14', 'fields' => ['Field 1' => 'val111', 'Field 2' => 'val131']],
                ['id' => 'rec15', 'fields' => ['Field 1' => 'val121', 'Field 2' => 'val141']],
                ['id' => 'rec16', 'fields' => ['Field 13' => 'val101', 'Field 2' => 'val111']],
            ]
        ])));

        $airtable = new AirTable($client->reveal());
        $airtable->setApiKey('apiKey123');
        $airtable->setBaseId('myBase');
        $airtable->setTableName('myTable');

        $response = $airtable->getIdsFromTable();
        $this->assertEquals([
            'rec11', 'rec12', 'rec13', 'rec14', 'rec15', 'rec16'
        ], $response);
    }
    
    /** @test */
    public function flushTable_deletes_all_rows(){
        $client = $this->prophesize(Client::class);
        $client->request('get', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'offset' => null
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec1', 'fields' => ['Field 1' => 'val11', 'Field 2' => 'val13']],
                ['id' => 'rec2', 'fields' => ['Field 1' => 'val12', 'Field 2' => 'val14']],
                ['id' => 'rec3', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
                ['id' => 'rec4', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
                ['id' => 'rec5', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
                ['id' => 'rec6', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
                ['id' => 'rec7', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
                ['id' => 'rec8', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
                ['id' => 'rec9', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
                ['id' => 'rec10', 'fields' => ['Field 13' => 'val10', 'Field 2' => 'val11']],
            ],
            'offset' => 'abc123'
        ])));
        $client->request('get', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'offset' => 'abc123'
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'records' => [
                ['id' => 'rec11', 'fields' => ['Field 1' => 'val111', 'Field 2' => 'val131']],
                ['id' => 'rec12', 'fields' => ['Field 1' => 'val121', 'Field 2' => 'val141']],
                ['id' => 'rec13', 'fields' => ['Field 13' => 'val101', 'Field 2' => 'val111']],
                ['id' => 'rec14', 'fields' => ['Field 13' => 'val101', 'Field 2' => 'val111']],
                ['id' => 'rec15', 'fields' => ['Field 13' => 'val101', 'Field 2' => 'val111']],
            ]
        ])));
        $client->request('delete', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'records' => [
                    'rec1', 'rec2', 'rec3', 'rec4', 'rec5', 'rec6', 'rec7', 'rec8', 'rec9', 'rec10'
                ]
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], ''));
        $client->request('delete', 'https://api.airtable.com/v0/myBase/myTable', [
            'headers' => [
                'Authorization' => 'Bearer apiKey123'
            ],
            'query' => [
                'records' => [
                    'rec11', 'rec12', 'rec13', 'rec14', 'rec15'
                ]
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], ''));

        $airtable = new AirTable($client->reveal());
        $airtable->setApiKey('apiKey123');
        $airtable->setBaseId('myBase');
        $airtable->setTableName('myTable');

        $airtable->flushTable();
    }
    
}