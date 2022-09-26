<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeiliSearchEngine;

class ScoutKeysCommand extends MeilisearchCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:keys {key? : UUID or key value to get information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get API keys information from Meilisearch';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($exitCode = $this->checkUsingMeilisearch()) {
            return $exitCode;
        }

        $dataKeys = [
            'uid', 'name', 'description', 'actions', 'indexes', 'key', 'expiresAt',
        ];

        $dataResult = $this->searchEngine->getRawKeys()['results'];

        if ($key = $this->argument('key')) {
            $dataResult = array_filter($dataResult, function ($item) use ($key) {
                return $item['uid'] === $key;
            });

            unset($dataKeys[0]);
        }

        if (empty($dataResult)) {
            $this->alert('No API keys found.');

            return 2;
        }

        $this->table(array_values($dataKeys), array_map(function ($item) {
            $item = [
                'uid' => $item['uid'],
                'name' => $item['name'],
                'description' => Str::limit($item['description'], 20),
                'actions' => implode(',', $item['actions']),
                'indexes' => implode(',', $item['indexes']),
                'key' => $item['key'],
                'expiresAt' => $item['expiresAt'] ? Carbon::make($item['expiresAt'])->locale('en')->diffForHumans() : 'never',
            ];

            if ($this->argument('key')) {
                unset($item['uid']);
            }

            return $item;
        }, $dataResult));

        return 0;
    }
}
