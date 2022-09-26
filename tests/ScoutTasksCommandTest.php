<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

class ScoutTasksCommandTest extends TestCase
{
    public function testScoutTasksCommandShowsTasksWithEnqueuedStatusNotFoundMessageWhenNoTasks()
    {
        $command = $this->artisan('scout:tasks');

        $command->assertExitCode(2);

        $command->expectsOutput('Tasks not found with status "enqueued".');
    }

    // TODO: Not that fast to catch Meilisearch engine... Need to enqueue a task for longer...
    // public function testScoutTasksCommandListEnqueuedTasksInATableByDefault()
    // {
    //     $this->searchEngine()->createIndex('genres');

    //     $command = $this->artisan('scout:tasks');

    //     $command->assertSuccessful();

    //     // TODO: Assert table shown 
    // }

    public function testScoutTasksCommandShowsWrongEngineMessageWhenNotUsingMeilisearch()
    {
        config(['scout.driver' => 'collection']);

        $command = $this->artisan('scout:tasks');

        $command->assertExitCode(1);

        $command->expectsOutput('Meilisearch is not the default Laravel Scout driver. This command only works with Meilisearch.');
    }
}
