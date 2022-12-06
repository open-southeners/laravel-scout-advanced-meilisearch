<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use MeiliSearch\Contracts\DeleteTasksQuery;

class ScoutTasksPruneCommand extends MeilisearchCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:tasks-prune
                            {--include-failed : Includes failed tasks to the deletion}
                            {--wait : Wait for task to finish to get a better result info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune finished Meilisearch tasks';

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

        $deleteTasksWithStatuses = ['succeeded', 'canceled'];

        if ($this->option('include-failed')) {
            $deleteTasksWithStatuses[] = 'failed';
        }

        $taskUid = $this->searchEngine->deleteTasks(
            (new DeleteTasksQuery)->setStatuses($deleteTasksWithStatuses)
        )['taskUid'] ?? null;

        if ($this->option('wait') && ! $this->hasTaskSucceed($this->gracefullyWaitForTask($taskUid))) {
            $this->error(sprintf('Tasks prune failed with task UID "%s"', $taskUid));

            return 2;
        }

        $this->info(
            sprintf(
                'Tasks with statuses "%s" has successfully been cleaned!',
                implode(', ', $deleteTasksWithStatuses)
            )
        );

        return 0;
    }
}
