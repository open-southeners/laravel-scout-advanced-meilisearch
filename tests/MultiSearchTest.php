<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use OpenSoutheners\LaravelScoutAdvancedMeilisearch\MultiSearch;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\Post;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures\User;
use Illuminate\Support\Collection;

class MultiSearchTest extends TestCase
{
    public function setUp(): void
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
        
        User::insert([
            [
                'name' => 'Joe Morgan',
                'email' => 'joe@example.org',
                'password' => '1234',
            ],
            [
                'name' => 'Traveling agency',
                'email' => 'agency@example.org',
                'password' => '1234',
            ],
        ]);
        
        $userInstance = new User;

        $this->createIndex($userInstance);
        
        User::makeAllSearchable();
    }
    
    public function testMultiSearchIsOnlyAvailableUsingMeilisearch()
    {
        config(['scout.driver' => 'null']);
        
        $this->expectExceptionMessage('Meilisearch is the only Scout engine that supports MultiSearch.');
        
        app(MultiSearch::class)->search('test');
    }
    
    public function testMultiSearchWithRawResultsGetsArrayWithMultipleModelsData()
    {
        $results = app(MultiSearch::class)
            ->setModelsPath(__DIR__.'/Fixtures')
            ->search('travel', true);
        
        $this->assertTrue($results instanceof Collection);
        $this->assertNotEmpty($results);
        $this->assertEquals('Travel cheap', $results[0]['hits'][0]['title'] ?? null);
        $this->assertEquals('Traveling agency', $results[1]['hits'][0]['name'] ?? null);
    }
}
