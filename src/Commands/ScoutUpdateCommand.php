<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Engines\MeilisearchEngine;
use Laravel\Scout\Searchable;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Contracts\ScoutSearchableModel;
use ReflectionClass;
use ReflectionMethod;

class ScoutUpdateCommand extends MeilisearchCommand
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
    protected $description = 'Update Meilisearch settings from model attribute';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modelClass = $this->argument('model');

        if (
            ! is_string($modelClass)
            || ! class_exists($modelClass)
            || ! is_a($modelClass, Model::class, true)
            || ! in_array(Searchable::class, class_uses_recursive($modelClass), true)
        ) {
            $this->error('This model is not searchable.');

            return 1;
        }

        /** @var Model&ScoutSearchableModel $model */
        $model = new $modelClass;

        $modelSearchEngine = $model->searchableUsing();

        if (! $modelSearchEngine instanceof MeilisearchEngine) {
            $this->error('Meilisearch is the only supported engine for the sorts and/or filters.');

            return 2;
        }

        $modelIndex = $model->searchableAs();

        $this->processTasks($model, $modelSearchEngine);

        $this->info("Index {$modelIndex} [{$modelClass}] settings updated successfully.");

        return 0;
    }

    /**
     * Get the searchable attribute instance, false otherwise.
     *
     * @param  Model&ScoutSearchableModel  $model
     */
    protected function processTasks(Model $model, MeilisearchEngine $engine): void
    {
        $modelSearchableAttribute = $this->getSearchableAttribute($model);

        /** @var \Meilisearch\Endpoints\Indexes $modelIndex */
        $modelIndex = $engine->__call('index', [$model->searchableAs()]);

        $tasks = [];

        if (! empty($searchableAttributes = $this->getSearchableAttributes($model, $modelSearchableAttribute))) {
            $tasks['Update searchable attributes'] = $modelIndex
                ->updateSearchableAttributes($searchableAttributes)['taskUid'] ?? null;
        }

        if (! empty($displayedAttributes = $this->getDisplayableAttributes($model, $modelSearchableAttribute))) {
            $tasks['Update displayable attributes'] = $modelIndex
                ->updateDisplayedAttributes($displayedAttributes)['taskUid'] ?? null;
        }

        if (! empty($filterableAttributes = $this->getFilterableAttributes($model, $modelSearchableAttribute))) {
            $tasks['Update filterable attributes'] = $modelIndex
                ->updateFilterableAttributes($filterableAttributes)['taskUid'] ?? null;
        }

        if (! empty($sortableAttributes = $this->getSortableAttributes($model, $modelSearchableAttribute))) {
            $tasks['Update sortable attributes'] = $modelIndex
                ->updateSortableAttributes($sortableAttributes)['taskUid'] ?? null;
        }

        if (empty($tasks) || ! $this->option('wait')) {
            return;
        }

        foreach ($tasks as $description => $taskUid) {
            $this->components->task($description, function () use ($taskUid) {
                return $this->hasTaskSucceed($this->gracefullyWaitForTask($taskUid));
            });
        }
    }

    /**
     * Get the searchable attribute instance, false otherwise.
     *
     * @param  object  $model
     * @return \OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings|false
     */
    protected function getSearchableAttribute($model)
    {
        $modelSearchableAttributes = (new ReflectionClass($model))->getAttributes(ScoutSearchableSettings::class);

        if ($modelSearchableAttributes === [] && method_exists($model, 'toSearchableArray')) {
            $modelSearchableAttributes = (new ReflectionMethod($model, 'toSearchableArray'))
                ->getAttributes(ScoutSearchableSettings::class);
        }

        if ($modelSearchableAttributes === []) {
            return false;
        }

        $searchableAttribute = head($modelSearchableAttributes);

        if ($searchableAttribute === null) {
            return false;
        }

        return $searchableAttribute->newInstance();
    }

    /**
     * Get attributes that are searchable from attribute or model.
     *
     * @param  Model&ScoutSearchableModel  $model
     * @param  false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings  $attribute
     * @return array<int, string>
     */
    protected function getSearchableAttributes(Model $model, $attribute): array
    {
        if ($attribute) {
            return array_values($attribute->searchable);
        }

        $displayableAttributes = $this->callOptionalStringArrayMethod($model, 'searchDisplayableAttributes');

        if ($displayableAttributes !== []) {
            return array_values(array_diff(array_map('strval', array_keys($model->toSearchableArray())), $displayableAttributes));
        }

        return [];
    }

    /**
     * Get attributes that are searchable from attribute or model.
     *
     * @param  Model&ScoutSearchableModel  $model
     * @param  false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings  $attribute
     * @return array<int, string>
     */
    protected function getDisplayableAttributes(Model $model, $attribute): array
    {
        if ($attribute) {
            return array_values($attribute->displayable);
        }

        return $this->callOptionalStringArrayMethod($model, 'searchDisplayableAttributes');
    }

    /**
     * Get attributes that are filterable from attribute or model.
     *
     * @param  Model&ScoutSearchableModel  $model
     * @param  false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings  $attribute
     * @return array<int, string>
     */
    protected function getSortableAttributes(Model $model, $attribute): array
    {
        if ($attribute) {
            return array_values($attribute->sortable);
        }

        return $this->callOptionalStringArrayMethod($model, 'searchableSorts');
    }

    /**
     * Get attributes that are filterable from attribute or model.
     *
     * @param  Model&ScoutSearchableModel  $model
     * @param  false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings  $attribute
     * @return array<int, string>
     */
    protected function getFilterableAttributes(Model $model, $attribute): array
    {
        if ($attribute) {
            return array_values($attribute->filterable);
        }

        return $this->callOptionalStringArrayMethod($model, 'searchableFilters');
    }

    /**
     * @param  object  $model
     * @return array<int, string>
     */
    protected function callOptionalStringArrayMethod(object $model, string $method): array
    {
        if (! method_exists($model, $method)) {
            return [];
        }

        $result = call_user_func([$model, $method]);

        if (! is_array($result)) {
            return [];
        }

        return array_values(array_map('strval', $result));
    }
}
