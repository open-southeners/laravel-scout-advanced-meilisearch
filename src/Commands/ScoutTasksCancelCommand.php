<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Support\Carbon;
use MeiliSearch\Contracts\CancelTasksQuery;

class ScoutTasksCancelCommand extends MeilisearchCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:tasks-cancel {tasks? : Tasks UIDs to cancel (one or comma delimited list)}
                            {--before-enqueued : Cancel tasks that were enqueued before date (enqueuedAt)}
                            {--after-enqueued : Cancel tasks that were enqueued after date (enqueuedAt)}
                            {--before-started : Cancel tasks that were started before date (startedAt)}
                            {--after-started : Cancel tasks that were started after date (startedAt)}
                            {--types : Cancel tasks by types (one or comma separated list)}
                            {--wait : Wait for task to finish to get a better result info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending tasks in Meilisearch';

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

        $query = $this->applyOptionsToQuery(new CancelTasksQuery);

        if ($tasks = $this->argument('tasks')) {
            $query->setIndexUids(explode(',', $tasks));
        }

        $taskUid = $this->searchEngine->cancelTasks($query)['taskUid'] ?? null;


        if ($this->option('wait') && ! $this->hasTaskSucceed($this->gracefullyWaitForTask($taskUid))) {
            $this->error(sprintf('Cancelling tasks failed using task UID "%s.'));

            return 2;
        }

        $this->info('Cancelling tasks succeeded!');

        return 0;
    }

    /**
     * Apply filters to query from parsed command options.
     * 
     * @param \MeiliSearch\Contracts\CancelTasksQuery $query
     * @return \MeiliSearch\Contracts\CancelTasksQuery
     */
    protected function applyOptionsToQuery(CancelTasksQuery $query)
    {
        $options = $this->options();

        if (isset($options['types'])) {
            $query->setTypes(explode(',', $options['types']));
        }

        if (isset($options['before-enqueued'])) {
            $query->setBeforeEnqueuedAt(Carbon::now()->sub($options['before-enqueued'])->toDateTime());
        }

        if (isset($options['after-enqueued'])) {
            $query->setAfterEnqueuedAt(Carbon::now()->add($options['after-enqueued'])->toDateTime());
        }
        
        if (isset($options['before-started'])) {
            $query->setBeforeStartedAt(Carbon::now()->sub($options['before-started'])->toDateTime());
        }
        
        if (isset($options['after-started'])) {
            $query->setAfterStartedAt(Carbon::now()->add($options['after-started'])->toDateTime());
        }

        return $query;
    }
}
