<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ScoutKeysCommandTest extends TestCase
{
    public function testScoutKeysCommandShowsListOfApiKeysOnATableWhenUidNotProvided()
    {
        $command = $this->artisan('scout:keys');

        $command->assertSuccessful();

        $apiKeysArr = array_map(function ($item) {
            $result = [
                'uid' => $item['uid'],
                'name' => $item['name'],
                'description' => Str::limit($item['description'], 20),
                'actions' => implode(',', $item['actions']),
                'indexes' => implode(',', $item['indexes']),
                'key' => $item['key'],
                'expiresAt' => $item['expiresAt'] ? Carbon::make($item['expiresAt'])->locale('en')->diffForHumans() : 'never',
            ];

            return $result;
        }, $this->searchEngine()->getRawKeys()['results']);

        $command->expectsTable([
            'uid', 'name', 'description', 'actions', 'indexes', 'key', 'expiresAt',
        ], $apiKeysArr);
    }

    public function testScoutKeysCommandShowsSingleApiKeyOnATableWhenUidProvided()
    {
        $apiKeysArr = $this->searchEngine()->getRawKeys()['results'];

        $command = $this->artisan('scout:keys', ['key' => head($apiKeysArr)['uid']]);

        $command->assertSuccessful();

        $apiKeysArr = array_map(function ($item) {
            $result = [
                'name' => $item['name'],
                'description' => Str::limit($item['description'], 20),
                'actions' => implode(',', $item['actions']),
                'indexes' => implode(',', $item['indexes']),
                'key' => $item['key'],
                'expiresAt' => $item['expiresAt'] ? Carbon::make($item['expiresAt'])->locale('en')->diffForHumans() : 'never',
            ];

            return $result;
        }, [head($apiKeysArr)]);

        $command->expectsTable([
            'name', 'description', 'actions', 'indexes', 'key', 'expiresAt',
        ], $apiKeysArr);
    }

    public function testScoutKeysCommandShowsNoApiKeyFoundMessageWhenWrongUidProvided()
    {
        $command = $this->artisan('scout:keys', ['key' => 'blablabla']);

        $command->assertExitCode(2);

        $command->expectsOutput('No API keys found.');
    }
    
    public function testScoutKeysCommandShowsWrongEngineWhenNotUsingMeilisearch()
    {
        config(['scout.driver' => 'collection']);

        $command = $this->artisan('scout:keys');

        $command->assertExitCode(1);

        $command->expectsOutput('Meilisearch is not the default Laravel Scout driver. This command only works with Meilisearch.');
    }
}
