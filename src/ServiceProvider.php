<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutTasksCancelCommand;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutTasksPruneCommand;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutUpdateCommand;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutDumpCommand;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutKeyCommand;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutKeysCommand;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutTasksCommand;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScoutKeyCommand::class,
                ScoutKeysCommand::class,
                ScoutUpdateCommand::class,
                ScoutTasksCommand::class,
                ScoutTasksCancelCommand::class,
                ScoutTasksPruneCommand::class,
                ScoutDumpCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
