<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Console\Command;
use Laravel\Scout\Searchable;
use function OpenSoutheners\LaravelHelpers\Classes\class_use;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes;
use ReflectionAttribute;
use ReflectionMethod;

class ScoutUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:update {model}';

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

        /** @var \Laravel\Scout\Searchable $model */
        $model = new $modelClass;

        /** @var \Meilisearch\Client $modelSearchEngine */
        $modelSearchEngine = $model->searchableUsing();

        if (get_class($modelSearchEngine) !== 'Laravel\Scout\Engines\MeiliSearchEngine') {
            $this->error('Meilisearch is the only supported engine for the filters.');

            return 1;
        }

        if (
            ! class_exists($modelClass)
            || ! class_use($modelClass, Searchable::class)
            // || ! method_exists($modelClass, 'toSearchableArray')
        ) {
            $this->error('This model does not have any filterable attribute configured or is not searchable.');

            return 2;
        }

        $modelSearchableAttribute = $this->getSearchableAttribute($model);

        $modelIndex = $model->searchableAs();

        $searchEngineIndex = $modelSearchEngine->index($modelIndex);

        $searchEngineIndex->updateFilterableAttributes($this->getFilterableAttributes($model, $modelSearchableAttribute));

        $searchEngineIndex->updateSortableAttributes($this->getSortableAttributes($model, $modelSearchableAttribute));

        $this->info("Updated attributes adding filterables and/or sortables for index ${modelIndex} [${modelClass}].");

        return 0;
    }

    /**
     * Get the searchable attribute instance, false otherwise.
     *
     * @param  object  $model
     * @return false|\OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes
     */
    public function getSearchableAttribute($model)
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            return false;
        }

        $reflectedMethod = new ReflectionMethod($model, 'toSearchableArray');

        $reflectedMethodAttributes = array_filter(
            $reflectedMethod->getAttributes(),
            function (ReflectionAttribute $reflectedAttribute) {
                return $reflectedAttribute->getName() === ScoutSearchableAttributes::class;
            }
        );

        if (empty($reflectedMethodAttributes)) {
            return false;
        }

        return head($reflectedMethodAttributes)->newInstance();
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
