<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeilisearchEngine;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Meilisearch\Contracts\SearchQuery;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings;

final class MultiSearch
{
    private MeilisearchEngine $searchEngine;

    /**
     * @var array<SearchQuery> $queries
     */
    private array $queries = [];

    /**
     * @var array<string, class-string<Model>> $indexModelMap
     */
    private array $indexModelMap = [];
    
    private ?string $modelsPath = null;

    public function __construct(EngineManager $engineManager)
    {
        if (! ($engineManager->engine() instanceof MeilisearchEngine)) {
            throw new Exception('Meilisearch is the only Scout engine that supports MultiSearch.');
        }

        $this->searchEngine = $engineManager->engine();
    }

    /**
     * Add searchable model class and query to the search.
     *
     * @param class-string<Model> $model
     */
    public function by(string $model, string $query): self
    {
        $indexUid = (new $model)->searchableAs();

        $this->indexModelMap[$indexUid] = $model;

        $this->queries[] = (new SearchQuery)->setIndexUid($indexUid)->setQuery($query);

        return $this;
    }

    /**
     * Get all models marked as globally searchable.
     *
     * @return array<string, class-string<Model>>
     */
    public static function getGloballySearchableModels(?string $path = null): array
    {
        $models = [];

        $fileResults = Finder::create()
            ->files()
            ->name('*.php')
            ->in($path ?? app_path('Models'));
        
        foreach ($fileResults as $file) {
            if (preg_match('#^namespace\s+(.+?);$#sm', $file->getContents(), $matches)) {
          		$baseNamespace = $matches[1];
           	}
            
            $reflector = new ReflectionClass($baseNamespace.'\\'.Str::beforeLast($file->getRelativePathname(), '.'));

            $attributes = $reflector->getAttributes(ScoutSearchableSettings::class)
                ?: ($reflector->hasMethod('toSearchableArray') ? $reflector->getMethod('toSearchableArray') : null)?->getAttributes(ScoutSearchableSettings::class)
                ?? [];

            $searchableAttribute = head($attributes);

            if (! $searchableAttribute) {
                continue;
            }

            /** @var ScoutSearchableSettings $attributeInstance */
            $attributeInstance = $searchableAttribute->newInstance();

            if (! $attributeInstance->globallySearchable) {
                continue;
            }

            $models[$reflector->newInstance()->searchableAs()] = $reflector->getName();
        }

        return $models;
    }

    /**
     * Set database builder query for global search.
     */
    private function setGlobalSearchQuery(string $query): void
    {
        foreach (static::getGloballySearchableModels($this->modelsPath) as $modelIndexUid => $modelClass) {
            $this->indexModelMap[$modelIndexUid] = $modelClass;

            $this->queries[] = (new SearchQuery)
                ->setIndexUid($modelIndexUid)
                ->setQuery($query);
        }
    }
    
    /**
     * Customise the default path for models files.
     */
    public function setModelsPath(string $path): self
    {
        $this->modelsPath = $path;
        
        return $this;
    }

    /**
     * Perform search from sent models or use globally searchable.
     */
    public function search(?string $query = null, bool $raw = false): Collection
    {
        if (empty($this->queries) && $query !== null) {
            $this->setGlobalSearchQuery($query);
        }

        $rawResults = Collection::make(
            $this->searchEngine->multiSearch($this->queries)['results']
        );

        if ($raw) {
            return $rawResults;
        }

        $results = Collection::make();

        $rawResults->each(function (array $result) use (&$results) {
            $model = new $this->indexModelMap[$result['indexUid']];
            $modelKeys = [];

            foreach ($result['hits'] as $resultHit) {
                $modelKeys[] = $resultHit[$model->getKeyName()];
            }

            $results = $results->merge($model->newQuery()->whereKey($modelKeys)->get());
        });

        return $results;
    }
}
