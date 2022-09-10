<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Post;

class ScoutDumpCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        $postInstance = new Post;

        $this->createIndex($postInstance);

        Post::makeAllSearchable();
    }
    
    public function testScoutDumpWillReturnSuccessWhenSearchDriverIsMeilisearch()
    {
        $command = $this->artisan('scout:dump');

        $command->assertSuccessful();

        $command->expectsOutput('Data dump created successfully with task status "enqueued".');
    }

    public function testScoutDumpWillReturnSuccessWithTaskStatusWhenWaitOptionSent()
    {
        $command = $this->artisan('scout:dump', ['--wait' => true]);

        $command->assertSuccessful();

        $command->expectsOutput('Data dump created successfully with task status "succeeded".');
    }

    public function testScoutDumpWillReturnErrorWhenSearchDriverIsNotMeilisearch()
    {
        config(['scout.driver' => 'collection']);

        $command = $this->artisan('scout:dump');

        $command->assertFailed();

        $command->expectsOutput('Meilisearch is not the default Laravel Scout driver. This command only works with Meilisearch.');
    }
}
