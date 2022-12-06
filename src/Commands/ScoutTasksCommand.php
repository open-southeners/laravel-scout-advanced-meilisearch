<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Support\Carbon;
use MeiliSearch\Contracts\TasksQuery;

class ScoutTasksCommand extends MeilisearchCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:tasks
                            {--status=enqueued : Filter tasks by status (enqueued, processing, succeeded or failed)}
                            {--limit=20 : Limit of tasks returned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Meilisearch tasks that are pending';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($exitCode = $this->checkUsingMeilisearch()) {
            return $exitCode;
        }

        $taskStatusFilter = $this->option('status');

        $tasks = $this->searchEngine->getTasks(
            (new TasksQuery())->setStatus([$taskStatusFilter])->setLimit($this->option('limit'))
        );

        $tasksResults = $tasks->getResults();

        if (empty($tasksResults)) {
            $this->warn(sprintf('Tasks not found with status "%s".', $taskStatusFilter));

            return 2;
        }

        $tasksResultsHeaders = ['uid', 'indexUid', 'type', 'duration', 'enqueuedAt', 'startedAt', 'finishedAt'];

        if ($taskStatusFilter === 'enqueued') {
            unset($tasksResultsHeaders[3]);
        }

        $this->table($tasksResultsHeaders, array_map(function ($task) {
            unset($task['status'], $task['details']);

            if ($task['duration'] ?? null) {
                $task['duration'] = strval((floatval(str_replace(['PT', 'S'], '', $task['duration'])) * 1000)).'ms';
            }

            $task['enqueuedAt'] = Carbon::make($task['enqueuedAt'])->diffForHumans();
            $task['startedAt'] = Carbon::make($task['startedAt'])->diffForHumans();
            $task['finishedAt'] = Carbon::make($task['finishedAt'])->diffForHumans();

            return $task;
        }, $tasksResults));

        return 0;
    }
}
