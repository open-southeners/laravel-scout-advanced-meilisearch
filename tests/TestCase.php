<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use Laravel\Scout\EngineManager;
use Laravel\Scout\ScoutServiceProvider;
use MeiliSearch\Contracts\TasksQuery;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @var \Laravel\Scout\Engines\MeilisearchEngine|\MeiliSearch\Client|null
     */
    protected $searchEngine;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            ScoutServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/database');
    }

    /**
     * Wait for all search engine tasks to complete for the given model instance.
     *
     * @param  \Laravel\Scout\Searchable  $model
     * @return void
     */
    protected function waitForAllSearchTasks($model)
    {
        /** @var \Laravel\Scout\Engines\MeilisearchEngine|\Meilisearch\Client $searchClient */
        $searchClient = $model->searchableUsing();

        $searchClient->waitForTasks(
            array_column($searchClient->getTasks((new TasksQuery())->setStatus(['processing']))->getResults(), 'uid')
        );
    }

    /**
     * Create search index for model instance.
     *
     * @param  \Laravel\Scout\Searchable  $model
     * @return \MeiliSearch\Endpoints\Indexes
     */
    protected function createIndex($model)
    {
        /** @var \Laravel\Scout\Engines\MeilisearchEngine|\Meilisearch\Client $searchClient */
        $searchClient = $model->searchableUsing();

        $response = $searchClient->createIndex($model->searchableAs());

        $searchClient->waitForTask($response['taskUid'] ?? $response['uid']);

        return $searchClient->getIndex($response['indexUid']);
    }

    /**
     * Get Meilisearch search engine from Laravel Scout.
     *
     * @return \Laravel\Scout\Engines\MeilisearchEngine|\MeiliSearch\Client
     */
    protected function searchEngine()
    {
        if (! $this->searchEngine) {
            $this->searchEngine = app(EngineManager::class)->engine('meilisearch');
        }

        return $this->searchEngine;
    }
}
