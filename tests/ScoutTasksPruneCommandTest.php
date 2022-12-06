<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use MeiliSearch\Contracts\TasksQuery;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Post;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\User;

class ScoutTasksPruneCommandTest extends TestCase
{
    public function testScoutTasksPruneCommandRemovesAllFinishedTasksWithSucceededAndCanceledStatuses()
    {
        Post::insert([
            [
                'title' => 'Hello world',
                'slug' => 'hello-world',
                'content' => 'Lorem ipsum dolor...',
            ],
            [
                'title' => 'I am a developer',
                'slug' => 'i-am-a-developer',
                'content' => 'Software developer from Spain',
            ],
            [
                'title' => 'Travel cheap',
                'slug' => 'travel-cheap',
                'content' => 'How to travel cheap',
            ],
        ]);

        $this->createIndex(new Post);

        Post::makeAllSearchable();
        
        $tasksBeforePrune = $this->searchEngine()->getTasks(
            (new TasksQuery)->setLimit(500)
        )->getResults();

        $command = $this->artisan('scout:tasks-prune', ['--wait' => true]);

        $command->assertSuccessful();
        
        $command->expectsOutput('Tasks with statuses "succeeded, canceled" has successfully been cleaned!');

        $command->run();

        $tasksAfterPrune = $this->searchEngine()->getTasks(
            (new TasksQuery)->setLimit(500)
        )->getResults();

        // print_r("\n");
        // print_r(reset($tasksBeforePrune));
        // print_r("\n");
        // print_r(reset($tasksAfterPrune));
        // print_r("\n");

        $this->assertTrue(reset($tasksAfterPrune)['type'] === 'taskDeletion');
        $this->assertTrue(reset($tasksAfterPrune)['status'] === 'succeeded');
        $this->assertTrue(reset($tasksAfterPrune)['details']['matchedTasks'] > 0);
        
        // print_r("\n");
        // print_r(count($tasksBeforePrune));
        // print_r("\n");
        // print_r(count($tasksAfterPrune));
        // print_r("\n");
        $this->assertTrue(count($tasksBeforePrune) > count($tasksAfterPrune));
    }

    public function testScoutTasksPruneCommandRemovesAllFinishedTasksIncludingFailedTasks()
    {
        $this->createIndex(new Post);

        $tasksBeforePrune = $this->searchEngine()->getTasks(
            (new TasksQuery)->setLimit(500)
        )->getResults();

        $command = $this->artisan('scout:tasks-prune', [
            '--wait' => true,
            '--include-failed' => true,
        ]);

        $command->assertSuccessful();
        
        $command->expectsOutput('Tasks with statuses "succeeded, canceled, failed" has successfully been cleaned!');

        $command->run();

        $tasksAfterPrune = $this->searchEngine()->getTasks(
            (new TasksQuery)->setLimit(500)
        )->getResults();

        $pruneTask = reset($tasksAfterPrune);

        $this->assertTrue($pruneTask['type'] === 'taskDeletion');
        $this->assertTrue($pruneTask['status'] === 'succeeded');
        $this->assertTrue($pruneTask['details']['matchedTasks'] > 0);

        $this->assertTrue(count($tasksBeforePrune) > count($tasksAfterPrune));
    }

    public function testScoutTasksPruneCommandShowsWrongEngineMessageWhenNotUsingMeilisearch()
    {
        config(['scout.driver' => 'collection']);

        $command = $this->artisan('scout:tasks-prune');

        $command->assertExitCode(1);

        $command->expectsOutput('Meilisearch is not the default Laravel Scout driver. This command only works with Meilisearch.');
    }
}
