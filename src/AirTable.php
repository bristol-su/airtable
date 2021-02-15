<?php

namespace BristolSU\AirTable;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class AirTable
{

    /**
     * The ID of the Progress base to use
     * 
     * @var string 
     */
    private string $baseId;

    /**
     * The name of the table to use
     *
     * @var string
     */
    private string $tableName;

    /**
     * API key to use for authentication
     * 
     * @var string 
     */
    private string $apiKey;

    /**
     * @var \GuzzleHttp\Client 
     */
    private \GuzzleHttp\Client $client;

    public static $rateLimitCooldown = 30;
    
    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getBaseId(): string
    {
        return $this->baseId;
    }

    /**
     * @param string $baseId
     */
    public function setBaseId(string $baseId): void
    {
        $this->baseId = $baseId;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $method
     * @param array|null $data
     * 
     * @return ResponseInterface
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request(string $method = 'get', array $data = null): ResponseInterface
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]
        ];
        if($data !== null) {
            if($method === 'get' || $method === 'delete') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }
        }
        return $this->client->request($method,
            sprintf('https://api.airtable.com/v0/%s/%s', $this->baseId, $this->tableName),
            $options
        );
    }
    
    protected function execute($dataChunk, \Closure $execution)
    {
        try {
            return $execution->call($this, $dataChunk);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            if($exception->getCode() === 429) {
                sleep(static::$rateLimitCooldown);
                return $this->execute($dataChunk, $execution);
            } else {
                throw $exception;
            }
        }
    }

    protected function chunkAndThrottle(array $data, \Closure $execution, int $delay = 1, int $chunkSize = 10)
    {
        $chunkedData = array_chunk($data, $chunkSize, false);
        foreach($chunkedData as $key => $dataChunk) {
            $this->execute($dataChunk, $execution);
            if($key !== array_key_last($chunkedData)) {
                sleep($delay);
            }
        }
    }

    /**
     * Create Rows
     *
     * @param array $rows An array of arrays with the key as the field name and the value as the field value. E.g.
     * [
     *      ['Field1' => 'Val1', 'Field2' => 'Val2'],
     *      ['Field1' => 'Val3', 'Field2' => 'Val3']
     * ]
     * @param bool $typecast
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createRows(array $rows, bool $typecast = true)
    {
        $data = [];
        foreach($rows as $row) {
            $data[] = ['fields' => $row];
        }

        $this->chunkAndThrottle($data, function($rowsToCreate) use ($typecast) {
            $this->request('post', [
                'records' => $rowsToCreate, 'typecast' => $typecast
            ]);
        });
    }


    /**
     * Update Rows.
     *
     * @param array $rows An array of data (see createRows for example)
     * @param bool $typecast
     */
    public function updateRows(array $rows, bool $typecast = true)
    {
        $data = [];
        foreach($rows as $row) {
            $data[] = ['id' => $row['id'], 'fields' => $row];
        }

        $this->chunkAndThrottle($data, function($rowsToCreate) use ($typecast) {
            $this->request('patch', [
                'records' => $rowsToCreate, 'typecast' => $typecast
            ]);
        });
    }

    /**
     * Delete Rows
     *
     * @param array $rowIds An array of row IDs to delete. E.g. ['rec123', 'rec456']
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteRows(array $rowIds)
    {
        $this->chunkAndThrottle($rowIds, function($rowIds) {
            $this->request('delete', [
                'records' => $rowIds
            ]);
        });
    }

    public function retrieveRecords()
    {
        $records = [];
        $offset = null;
        do {
            $response = $this->execute([], function($data) use ($offset) {
                return $this->request('get', [
                    'offset' => $offset
                ]);
            });
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            try {
                $newRecords = $responseData['records'];
            } catch (\Exception $e) {
                $newRecords = [];
            }
            
            $records = array_merge($records, $newRecords);
            if(array_key_exists('offset', $responseData)) {
                $offset = $responseData['offset'];
            } else {
                $offset = null;
            }
        } while($offset !== null);
        return $records;
    }
    
    public function getIdsFromTable(): array
    {
         $ids = [];
        foreach($this->retrieveRecords() as $record) {
            $ids[] = $record['id'];
        }
        return $ids;
    }

    public function flushTable()
    {
        $this->deleteRows(
            $this->getIdsFromTable()
        );
    }
    
}