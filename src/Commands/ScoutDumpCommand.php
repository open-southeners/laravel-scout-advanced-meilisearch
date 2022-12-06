<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

class ScoutDumpCommand extends MeilisearchCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:dump {--wait : Wait for task to finish to get a better result info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create data dump from current Meilisearch state (use ONLY with Meilisearch driver)';

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

        $task = $this->gracefullyWaitForTask($this->searchEngine->createDump());

        $this->info(sprintf('Data dump created successfully with task status "%s".', $task['status']));

        return 0;
    }
}
