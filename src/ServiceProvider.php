<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutUpdateCommand;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands\ScoutDumpCommand;

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
                ScoutUpdateCommand::class,
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
