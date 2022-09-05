<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use Illuminate\Support\Facades\Artisan;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Post;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Tag;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\User;

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

        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $postSearchEngine */
        $postSearchEngine = $postInstance->searchableUsing();

        $postSearchIndexKey = $postInstance->searchableAs();

        Artisan::call('scout:index', ['name' => $postSearchIndexKey]);
        Post::makeAllSearchable();

        $command = $this->artisan('scout:update', [
            'model' => Post::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutput("Updated attributes adding filterables and/or sortables for index posts [".Post::class."].");

        $command->execute();

        $postSearchIndex = $postSearchEngine->index($postSearchIndexKey);

        $this->assertEmpty(array_diff($postSearchIndex->getFilterableAttributes(), ['title']));
        $this->assertEmpty(array_diff($postSearchIndex->getSortableAttributes(), ['slug']));
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

        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $userSearchEngine */
        $userSearchEngine = $userInstance->searchableUsing();

        $userSearchIndexKey = $userInstance->searchableAs();

        Artisan::call('scout:index', ['name' => $userSearchIndexKey]);
        User::makeAllSearchable();

        $command = $this->artisan('scout:update', [
            'model' => User::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutput("Updated attributes adding filterables and/or sortables for index users [".User::class."].");

        $command->execute();

        $userSearchIndex = $userSearchEngine->index($userSearchIndexKey);

        $this->assertEmpty(array_diff($userSearchIndex->getFilterableAttributes(), ['email']));
        $this->assertEmpty(array_diff($userSearchIndex->getSortableAttributes(), ['name']));
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

        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $tagSearchEngine */
        $tagSearchEngine = $tagInstance->searchableUsing();

        $tagSearchIndexKey = $tagInstance->searchableAs();

        Artisan::call('scout:index', ['name' => $tagSearchIndexKey]);
        Tag::makeAllSearchable();

        $command = $this->artisan('scout:update', [
            'model' => Tag::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutput("Updated attributes adding filterables and/or sortables for index tags [".Tag::class."].");

        $command->execute();

        $tagSearchIndex = $tagSearchEngine->index($tagSearchIndexKey);

        $this->assertEmpty(array_diff($tagSearchIndex->getFilterableAttributes(), ['name']));
        $this->assertEmpty(array_diff($tagSearchIndex->getSortableAttributes(), ['slug']));
    }
}
