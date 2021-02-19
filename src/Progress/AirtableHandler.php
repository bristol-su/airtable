<?php


namespace BristolSU\AirTable\Progress;

use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\AirTable\Jobs\UpdateRecords;
use BristolSU\AirTable\Models\AirtableId;
use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceRepository;
use BristolSU\Support\ModuleInstance\Contracts\ModuleInstanceRepository;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use BristolSU\Support\Progress\Handlers\Handler;
use BristolSU\Support\Progress\ModuleInstanceProgress;
use BristolSU\Support\Progress\Progress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AirtableHandler implements Handler
{

    protected string $baseId;
    protected string $tableName;
    protected string $apiKey;
    private bool $debug;

    public function __construct(string $baseId, string $tableName, string $apiKey, bool $debug = false)
    {
        $this->baseId = $baseId;
        $this->tableName = $tableName;
        $this->apiKey = $apiKey;
        $this->debug = $debug;
    }

    protected function filterModules(\Closure $filter, Progress $progress, $moduleInstances)
    {
        return collect($progress->getModules())
            ->filter($filter)->map(function (ModuleInstanceProgress $moduleInstanceProgress) use ($moduleInstances) {
                return $moduleInstances[$moduleInstanceProgress->getModuleInstanceId()];
            })->values()->toArray();
    }

    protected function parseProgress(Progress $progress, bool $includeId = false)
    {
        $activityInstance = app(ActivityInstanceRepository::class)
            ->getById($progress->getActivityInstanceId());
        $moduleInstances = app(ModuleInstanceRepository::class)
            ->allThroughActivity($activityInstance->activity)
            ->reduce(function ($carry, ModuleInstance $moduleInstance) {
                $carry[$moduleInstance->id()] = $moduleInstance->name;
                return $carry;
            });

        return [
            'Participant Name' => $activityInstance->participantName(),
            'Mandatory Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return $moduleInstanceProgress->isMandatory();
            }, $progress, $moduleInstances),
            'Optional Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return !$moduleInstanceProgress->isMandatory();
            }, $progress, $moduleInstances),
            'Complete Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return $moduleInstanceProgress->isComplete();
            }, $progress, $moduleInstances),
            'Incomplete Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return !$moduleInstanceProgress->isComplete();
            }, $progress, $moduleInstances),
            'Active Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return $moduleInstanceProgress->isActive();
            }, $progress, $moduleInstances),
            'Inactive Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return !$moduleInstanceProgress->isActive();
            }, $progress, $moduleInstances),
            'Hidden Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return !$moduleInstanceProgress->isVisible();
            }, $progress, $moduleInstances),
            'Visible Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return $moduleInstanceProgress->isVisible();
            }, $progress, $moduleInstances),
            '% Complete' => $progress->getPercentage(),
            'Activity Instance ID' => $activityInstance->id,
            'Activity ID' => $progress->getActivityId(),
            'Participant ID' => $activityInstance->resource_id,
            'Snapshot Date' => $progress->getTimestamp()->format(\DateTime::ATOM)
        ];
    }

    /**
     * @param array|Progress[] $progresses
     */
    public function saveMany(array $progresses): void
    {
        $airTableID = new AirtableId();

        $create = [];
        $update = [];
        $this->log(sprintf('Parsing %d progresses', count($progresses)));
        foreach ($progresses as $progress) {
            if($airTableID->hasActivityInstance($progress->getActivityInstanceId())) {
                $update[] = ['id' => $airTableID->getRowId($progress->getActivityInstanceId()), 'fields' => $this->parseProgress($progress)];
            } else {
                $create[] = $this->parseProgress($progress);
            }
        }

        if($create) { $this->createRecords($create); }
        if($update) { $this->updateRecords($update); }
    }

    public function save(Progress $progress): void
    {
        $this->saveMany([$progress]);
    }

    protected function createRecords(array $data)
    {

        foreach(array_chunk($data, 10) as $rows) {
            dispatch((new CreateRecords(
                $rows,
                $this->apiKey,
                $this->baseId,
                $this->tableName
            ))->withDebug($this->debug));
        }
        
        $this->log('Created Records');
    }

    public function updateRecords(array $data)
    {
        $this->log('Updating records');

        foreach(array_chunk($data, 10) as $rows) {
            dispatch((new UpdateRecords(
                $rows,
                $this->apiKey,
                $this->baseId,
                $this->tableName
            ))->withDebug($this->debug));
        }

        $this->log('Updated Records');
    }

    private function log(string $string)
    {
        if($this->debug) {
            Log::debug($string);
        }
    }
}
