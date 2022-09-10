<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Console\Command;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeiliSearchEngine;
use MeiliSearch\Exceptions\TimeOutException;

class ScoutDumpCommand extends Command
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected EngineManager $searchEngineManager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $searchEngine = $this->searchEngineManager->engine();

        if (! $searchEngine instanceof MeiliSearchEngine) {
            $this->error('Meilisearch is not the default Laravel Scout driver. This command only works with Meilisearch.');

            return 1;
        }

        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client $searchEngine */
        $task = $searchEngine->createDump();

        if ($this->option('wait')) {
            try {
                $task = $searchEngine->waitForTask($task['taskUid'] ?? $task['uid']);
            } catch (TimeOutException $e) {
                $this->warn('Waiting for Meilisearch task timed out.');
            }
        }

        $this->info(sprintf('Data dump created successfully with task status "%s".', $task['status']));

        return 0;
    }
}
