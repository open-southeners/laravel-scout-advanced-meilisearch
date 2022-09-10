<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Console\Command;
use Laravel\Scout\Searchable;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes;
use ReflectionClass;
use ReflectionMethod;
use MeiliSearch\Exceptions\TimeOutException;

class ScoutUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:update {model}
                            {--wait : Wait for task to finish to get a better result info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update filters and sorts from model attributes into the Scout engine (Meilisearch only)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modelClass = $this->argument('model');

        if (
            ! class_exists($modelClass)
            || ! in_array(Searchable::class, class_uses($modelClass))
        ) {
            $this->error('This model is not searchable.');

            return 1;
        }

        /** @var \Laravel\Scout\Searchable $model */
        $model = new $modelClass;

        /** @var \Meilisearch\Client $modelSearchEngine */
        $modelSearchEngine = $model->searchableUsing();

        if (get_class($modelSearchEngine) !== 'Laravel\Scout\Engines\MeiliSearchEngine') {
            $this->error('Meilisearch is the only supported engine for the sorts and/or filters.');

            return 2;
        }

        $modelIndex = $model->searchableAs();

        $this->processTasks($model, $modelSearchEngine);

        $this->info("Index ${modelIndex} [${modelClass}] settings updated successfully.");

        return 0;
    }

    /**
     * Get the searchable attribute instance, false otherwise.
     *
     * @param  \Laravel\Scout\Searchable  $model
     * @param  \Meilisearch\Client  $engine
     * @return void
     */
    protected function processTasks($model, $engine)
    {
        $modelSearchableAttribute = $this->getSearchableAttribute($model);

        $modelIndex = $engine->index($model->searchableAs());

        $tasks = array_filter([
            'Update filterable attributes' => $modelIndex->updateFilterableAttributes(
                $this->getFilterableAttributes($model, $modelSearchableAttribute)
            )['taskUid'] ?? null,
            'Update sortable attributes' => $modelIndex->updateSortableAttributes(
                $this->getSortableAttributes($model, $modelSearchableAttribute)
            )['taskUid'] ?? null,
        ]);

        if (empty($tasks) || ! $this->option('wait')) {
            return;
        }

        foreach ($tasks as $description => $taskUid) {
            // @codeCoverageIgnoreStart
            if (! property_exists($this, 'components')) {
                $taskDoneSuccessfully = $this->waitForTask($engine, $taskUid);

                $this->line(
                    $description.' done '.($taskDoneSuccessfully ? 'successfully' : 'unsuccessfully'),
                    $taskDoneSuccessfully ? 'info' : 'error'
                );

                continue;
            }
            // @codeCoverageIgnoreEnd

            $this->components->task($description, function () use ($taskUid, $engine) {
                return $this->waitForTask($engine, $taskUid);
            });
        }
    }

    /**
     * Wait for task without handling timeout exception.
     * 
     * @param  \Meilisearch\Client  $engine
     * @param mixed $taskUid
     * @return bool
     */
    protected function waitForTask($engine, $taskUid)
    {
        try {
            $task = $engine->waitForTask($taskUid);

            return $task['status'] === 'succeeded';
        // @codeCoverageIgnoreStart
        } catch (TimeOutException $e) {
            return false;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Get the searchable attribute instance, false otherwise.
     *
     * @param  object  $model
     * @return false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes
     */
    protected function getSearchableAttribute($model)
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $modelSearchableAttributes = (new ReflectionClass($model))->getAttributes(ScoutSearchableAttributes::class);

        if (empty($modelSearchableAttributes)) {
            $modelSearchableAttributes = (new ReflectionMethod($model, 'toSearchableArray'))
                ->getAttributes(ScoutSearchableAttributes::class);
        }

        if (empty($modelSearchableAttributes)) {
            return false;
        }

        return head($modelSearchableAttributes)->newInstance();
    }

    /**
     * Get attributes that are filterable from attribute or model.
     *
     * @param  \Laravel\Scout\Searchable  $model
     * @param  false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes  $attribute
     * @return array
     */
    protected function getSortableAttributes($model, $attribute)
    {
        if ($attribute) {
            return $attribute->sortable;
        }

        if (method_exists($model, 'searchableSorts')) {
            return $model->searchableSorts();
        }

        return [];
    }

    /**
     * Get attributes that are filterable from attribute or model.
     *
     * @param  \Laravel\Scout\Searchable  $model
     * @param  false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes  $attribute
     * @return array
     */
    protected function getFilterableAttributes($model, $attribute)
    {
        if ($attribute) {
            return $attribute->filterable;
        }

        if (method_exists($model, 'searchableFilters')) {
            return $model->searchableFilters();
        }

        return [];
    }
}
