<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

class ScoutKeyCommandTest extends TestCase
{
    public function testScoutKeyCommandCreatesApiKeyWhenCreateOptionIsSent()
    {
        $this->searchEngine()->waitForTask($this->searchEngine()->createIndex('films')['taskUid']);

        $command = $this->artisan('scout:key', ['--create' => true]);

        $command->expectsQuestion('Comma separated list of API actions to be allowed for this key', 'search,documents.get');
        $command->expectsQuestion('Comma separated list of indexes the key is authorized to act on', 'films');
        $command->expectsQuestion('Date and time when the key will expire (e.g. 1 hour, 6 months, 10 years...)', '6 months');
        $command->expectsQuestion('A human-readable name for the key', 'films.search');
        $command->expectsQuestion('An optional description for the key', 'Search films database');

        $command->assertSuccessful();

        $command->expectsOutput('Key creation action performed successfully!');

        $command->execute();

        $this->assertEquals(head($this->searchEngine()->getRawKeys()['results'])['name'], 'films.search');
    }
    
    public function testScoutKeyCommandUpdatesApiKeyWhenUpdateOptionIsSentWithAKey()
    {
        $latestApiKey = head($this->searchEngine()->getRawKeys()['results']);
        
        $command = $this->artisan('scout:key', ['key' => $latestApiKey['uid'], '--update' => true]);
        
        $command->expectsQuestion('A human-readable name for the key', 'films.search');
        $command->expectsQuestion('An optional description for the key', 'Search films database (no longer used)');
        
        $command->assertSuccessful();
        
        $command->expectsOutput('Key modification action performed successfully!');
        
        $command->execute();
        
        $updatedApiKey = $this->searchEngine()->getKey($latestApiKey['uid']);

        $this->assertEquals($updatedApiKey->getDescription(), 'Search films database (no longer used)');
    }

    public function testScoutKeyCommandDeletesApiKeyWhenDeleteOptionIsSentWithAKey()
    {
        $apiKeyResultsArr = $this->searchEngine()->getRawKeys()['results'];
        $latestApiKey = head($apiKeyResultsArr);
        
        $command = $this->artisan('scout:key', ['key' => $latestApiKey['uid'], '--delete' => true]);
        
        $command->expectsConfirmation(sprintf('Are you sure you want to delete "%s" API key?', $latestApiKey['uid']), 'yes');
        
        $command->assertSuccessful();
        
        $command->expectsOutput('Key deletion action performed successfully!');
        
        $command->execute();
        
        $newApiKeyResultsArr = $this->searchEngine()->getRawKeys()['results'];

        $this->assertNotEquals(count($apiKeyResultsArr), count($newApiKeyResultsArr));
    }
    
    public function testScoutKeyCommandShowsNoKeyMessageWhenUpdateOptionSentWithoutKeyArgument()
    {
        $command = $this->artisan('scout:key', ['--update' => true]);

        $command->assertExitCode(2);

        $command->expectsOutput('You need to pass a key value or UUID to be able to perform this action.');
    }

    public function testScoutKeyCommandShowsNoKeyMessageWhenDeleteOptionSentWithoutKeyArgument()
    {
        $command = $this->artisan('scout:key', ['--delete' => true]);

        $command->assertExitCode(2);

        $command->expectsOutput('You need to pass a key value or UUID to be able to perform this action.');
    }

    public function testScoutKeyCommandShowsWrongEngineMessageWhenNotUsingMeilisearch()
    {
        config(['scout.driver' => 'collection']);

        $command = $this->artisan('scout:key');

        $command->assertExitCode(1);

        $command->expectsOutput('Meilisearch is not the default Laravel Scout driver. This command only works with Meilisearch.');
    }
}
