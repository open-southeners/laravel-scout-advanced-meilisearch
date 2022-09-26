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

        // $command->execute();

        $this->assertEquals(head($this->searchEngine()->getRawKeys()['results'])['name'], 'films.search');
    }
}
