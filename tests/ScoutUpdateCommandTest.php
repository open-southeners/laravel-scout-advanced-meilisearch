<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Comment;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Country;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Post;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Tag;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\User;
use MeiliSearch\Contracts\TasksQuery;

class ScoutUpdateCommandTest extends TestCase
{
    /**
     * @group needsPhp8
     */
    public function testScoutUpdateCommandChangesFiltersAndSortsUsingAttributeAtMethodLevel()
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

        $postInstance = new Post;

        $postSearchIndex = $this->createIndex($postInstance);

        Post::makeAllSearchable();

        $command = $this->artisan('scout:update', [
            'model' => Post::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutput("Updated attributes adding filterables and/or sortables for index {$postSearchIndex->getUid()} [".Post::class.'].');

        $command->execute();

        $this->waitForAllSearchTasks($postInstance);

        $postSearchFilterableAttributes = $postSearchIndex->getFilterableAttributes();
        $postSearchSortableAttributes = $postSearchIndex->getSortableAttributes();

        $this->assertNotEmpty($postSearchFilterableAttributes);
        $this->assertNotEmpty($postSearchSortableAttributes);

        $this->assertEmpty(array_diff(['title'], $postSearchFilterableAttributes));
        $this->assertEmpty(array_diff(['slug'], $postSearchSortableAttributes));
    }

    /**
     * @group needsPhp8
     */
    public function testScoutUpdateCommandChangesFiltersAndSortsUsingAttributeAtClassLevel()
    {
        User::insert([
            [
                'name' => 'Ruben Robles',
                'email' => 'ruben@helloworld.com',
                'password' => '1234',
            ],
            [
                'name' => 'Daniel Perez',
                'email' => 'daniel@helloworld.com',
                'password' => '1234',
            ],
        ]);

        $userInstance = new User;

        $userSearchIndex = $this->createIndex($userInstance);

        User::makeAllSearchable();

        $command = $this->artisan('scout:update', [
            'model' => User::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutput("Updated attributes adding filterables and/or sortables for index {$userSearchIndex->getUid()} [".User::class.'].');

        $command->execute();

        $this->waitForAllSearchTasks($userInstance);

        $userSearchFilterableAttributes = $userSearchIndex->getFilterableAttributes();
        $userSearchSortableAttributes = $userSearchIndex->getSortableAttributes();

        $this->assertNotEmpty($userSearchFilterableAttributes);
        $this->assertNotEmpty($userSearchSortableAttributes);

        $this->assertEmpty(array_diff(['email'], $userSearchFilterableAttributes));
        $this->assertEmpty(array_diff(['name'], $userSearchSortableAttributes));
    }

    public function testScoutUpdateCommandChangesFiltersAndSortsUsingMethods()
    {
        Tag::insert([
            [
                'name' => 'Hello world',
                'slug' => 'hello-world',
            ],
            [
                'name' => 'Traveling',
                'email' => 'traveling',
            ],
            [
                'name' => 'Cooking',
                'email' => 'cooking',
            ],
        ]);

        $tagInstance = new Tag;

        $tagSearchIndex = $this->createIndex($tagInstance);

        Tag::makeAllSearchable();

        $command = $this->artisan('scout:update', [
            'model' => Tag::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutput("Updated attributes adding filterables and/or sortables for index {$tagSearchIndex->getUid()} [".Tag::class.'].');

        $command->execute();

        $this->waitForAllSearchTasks($tagInstance);

        $tagSearchFilterableAttributes = $tagSearchIndex->getFilterableAttributes();
        $tagSearchSortableAttributes = $tagSearchIndex->getSortableAttributes();

        $this->assertNotEmpty($tagSearchFilterableAttributes);
        $this->assertNotEmpty($tagSearchSortableAttributes);

        $this->assertEmpty(array_diff(['name'], $tagSearchFilterableAttributes));
        $this->assertEmpty(array_diff(['slug'], $tagSearchSortableAttributes));
    }

    public function testScoutUpdateCommandWhenModelIsNotSearchableReturnsError()
    {
        $command = $this->artisan('scout:update', [
            'model' => Comment::class,
        ]);

        $command->assertFailed();

        $command->expectsOutput('This model is not searchable.');
    }

    public function testScoutUpdateCommandWhenModelIsNotSearchableThroughMeilisearchReturnsError()
    {
        $command = $this->artisan('scout:update', [
            'model' => Country::class,
        ]);

        $command->assertFailed();

        $command->expectsOutput('Meilisearch is the only supported engine for the sorts and/or filters.');
    }

    /**
     * Wait for all search engine tasks to complete for the given model instance.
     *
     * @param  \Laravel\Scout\Searchable  $model
     * @return void
     */
    private function waitForAllSearchTasks($model)
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $searchClient */
        $searchClient = $model->searchableUsing();

        $searchClient->waitForTasks(
            $searchClient->getTasks((new TasksQuery())->setStatus(['processing']))->getResults()
        );
    }

    /**
     * Create search index for model instance.
     *
     * @param  \Laravel\Scout\Searchable  $model
     * @return \MeiliSearch\Endpoints\Indexes
     */
    private function createIndex($model)
    {
        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $searchClient */
        $searchClient = $model->searchableUsing();

        $response = $searchClient->createIndex($model->searchableAs());

        $searchClient->waitForTask($response['taskUid'] ?? $response['uid']);

        return $searchClient->getIndex($response['indexUid']);
    }
}
