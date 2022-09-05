<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use Illuminate\Support\Facades\Artisan;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Post;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Tag;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\User;

class ScoutUpdateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

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

        Artisan::call('scout:index', ['name' => 'posts']);
        Artisan::call('scout:index', ['name' => 'users']);
        Artisan::call('scout:index', ['name' => 'tags']);
        Post::makeAllSearchable();
        User::makeAllSearchable();
        Tag::makeAllSearchable();
    }

    /**
     * @group needsPhp8
     */
    public function testScoutUpdateCommandChangesFiltersAndSortsUsingAttributeAtMethodLevel()
    {
        $command = $this->artisan('scout:update', [
            'model' => Post::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutputToContain('Updated attributes');

        $command->execute();

        $postInstance = new Post;

        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $postSearchEngine */
        $postSearchEngine = $postInstance->searchableUsing();

        $postSearchIndex = $postSearchEngine->index($postInstance->searchableAs());

        $this->assertEmpty(array_diff($postSearchIndex->getFilterableAttributes(), ['title']));
        $this->assertEmpty(array_diff($postSearchIndex->getSortableAttributes(), ['slug']));
    }

    /**
     * @group needsPhp8
     */
    public function testScoutUpdateCommandChangesFiltersAndSortsUsingAttributeAtClassLevel()
    {
        $command = $this->artisan('scout:update', [
            'model' => User::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutputToContain('Updated attributes');

        $command->execute();

        $userInstance = new User;

        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $userSearchEngine */
        $userSearchEngine = $userInstance->searchableUsing();

        $userSearchIndex = $userSearchEngine->index($userInstance->searchableAs());

        $this->assertEmpty(array_diff($userSearchIndex->getFilterableAttributes(), ['email']));
        $this->assertEmpty(array_diff($userSearchIndex->getSortableAttributes(), ['name']));
    }

    public function testScoutUpdateCommandChangesFiltersAndSortsUsingMethods()
    {
        $command = $this->artisan('scout:update', [
            'model' => Tag::class,
        ]);

        $command->assertSuccessful();

        $command->expectsOutputToContain('Updated attributes');

        $command->execute();

        $tagInstance = new Tag;

        /** @var \Laravel\Scout\Engines\MeiliSearchEngine|\Meilisearch\Client $tagSearchEngine */
        $tagSearchEngine = $tagInstance->searchableUsing();

        $tagSearchIndex = $tagSearchEngine->index($tagInstance->searchableAs());

        $this->assertEmpty(array_diff($tagSearchIndex->getFilterableAttributes(), ['name']));
        $this->assertEmpty(array_diff($tagSearchIndex->getSortableAttributes(), ['slug']));
    }
}
