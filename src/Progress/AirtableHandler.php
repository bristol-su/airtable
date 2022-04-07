<?php


namespace BristolSU\AirTable\Progress;

use BristolSU\AirTable\AirtableIdManager;
use BristolSU\AirTable\Jobs\CreateProgressRecords;
use BristolSU\AirTable\Jobs\CreateRecords;
use BristolSU\AirTable\Jobs\UpdateRecords;
use BristolSU\AirTable\Models\AirtableId;
use BristolSU\Module\DataEntry\Models\ActivityInstance;
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

    protected function parseProgress(Progress $progress)
    {
        /** @var ActivityInstance $activityInstance */
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
            'Remaining Modules' => $this->filterModules(function (ModuleInstanceProgress $moduleInstanceProgress) {
                return $moduleInstanceProgress->isMandatory() && !$moduleInstanceProgress->isComplete();
            }, $progress, $moduleInstances),
            'Tags' => $activityInstance->participant->tags()->map(fn($tag) => $tag->full_reference)->toArray(),
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
        $toCreate = [];
        $toUpdate = [];

        /** @var AirtableIdManager $airtableIdManager */
        $airtableIdManager = app(AirtableIdManager::class);

        foreach ($progresses as $progress) {
            $parsedProgress = $this->parseProgress($progress);
            if ($airtableIdManager->hasModel($progress->getActivityInstanceId(), 'progress_' . $this->tableName . '_' . $this->baseId)) {
                $toUpdate[] = [
                    'id' => $airtableIdManager->getAirtableId($progress->getActivityInstanceId(), 'progress_' . $this->tableName . '_' . $this->baseId),
                    'fields' => $parsedProgress
                ];
            } else {
                $toCreate[] = [
                    'fields' => $parsedProgress
                ];
            }
        }

        $this->log(sprintf('Creating %d progress rows', count($toCreate)));
        $this->log(sprintf('Updating %d progress rows', count($toUpdate)));

        if ($toCreate) {
            $this->createRecords($toCreate);
        }
        if ($toUpdate) {
            $this->updateRecords($toUpdate);
        }
    }

    public function save(Progress $progress): void
    {
        $this->saveMany([$progress]);
    }

    protected function createRecords(array $data)
    {
        foreach (array_chunk($data, 10) as $rows) {
            dispatch((new CreateProgressRecords(
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
        foreach (array_chunk($data, 10) as $rows) {
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
        if ($this->debug) {
            Log::debug($string);
        }
    }
}
