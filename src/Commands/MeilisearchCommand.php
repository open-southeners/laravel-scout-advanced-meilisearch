<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Console\Command;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeiliSearchEngine;
use MeiliSearch\Exceptions\TimeOutException;

class MeilisearchCommand extends Command
{
    /**
     * @var \Laravel\Scout\Engines\Engine|\Laravel\Scout\Engines\MeiliSearchEngine|\MeiliSearch\Client
     */
    protected $searchEngine;

    /**
     * Create a new command instance.
     *
     * @param \Laravel\Scout\EngineManager $engineManager
     * @return void
     */
    public function __construct(EngineManager $engineManager)
    {
        parent::__construct();

        $this->searchEngine = $engineManager->engine();
    }

    /**
     * Check if current Scout engine is Meilisearch.
     * 
     * @return int
     */
    protected function checkUsingMeilisearch()
    {
        if (! $this->searchEngine instanceof MeiliSearchEngine) {
            $this->error('Meilisearch is not the default Laravel Scout driver. This command only works with Meilisearch.');

            return 1;
        }

        return 0;
    }

    /**
     * Wait for Meilisearch task to finish without throwing timeout error.
     * 
     * @param mixed $task
     * @return array
     */
    protected function gracefullyWaitForTask($task)
    {
        if (! $this->hasOption('wait') || ! $this->option('wait')) {
            return $task;
        }

        $resolvedTask = [];

        try {
            $resolvedTask = $this->searchEngine->waitForTask($task['taskUid'] ?? $task['uid'] ?? $task);
        // @codeCoverageIgnoreStart
        } catch (TimeOutException $e) {
            $this->warn('Waiting for Meilisearch task timed out.');
            // @codeCoverageIgnoreEnd
        }

        return $resolvedTask;
    }

    /**
     * Check if resulted task resolution has been succeeded.
     * 
     * @param mixed $task
     * @return bool
     */
    protected function hasTaskSucceed($task)
    {
        if (isset($task['status'])) {
            return $task['status'] === 'succeeded';
        }

        return false;
    }

    /**
     * Prompt the user for comma-delimited input with auto completion.
     * 
     * @codeCoverageIgnore
     * @param string $question
     * @param array $choices
     * @param string|null $default
     * @return mixed
     */
    protected function askWithCompletionList(string $question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, function (string $input) use ($choices) {
            $inputArr = explode(',', $input);
            $inputArrWithoutLast = $inputArr;
            $lastInput = array_pop($inputArrWithoutLast);
            $actions = array_filter($choices, function ($action) use ($inputArrWithoutLast) {
                return ! in_array($action, $inputArrWithoutLast);
            });

            if (! empty($inputArrWithoutLast)) {
                $inputArrWithoutLast = implode(',', $inputArrWithoutLast);

                $actions = array_map(function ($action) use ($inputArrWithoutLast) {
                    return "${inputArrWithoutLast},${action}";
                }, $actions);
            }

            return array_filter($actions, function ($action) use ($lastInput) {
                return str_contains($action, $lastInput);
            });
        });
    }
}
