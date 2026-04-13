<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Contracts;

use Laravel\Scout\Engines\Engine;

interface ScoutSearchableModel
{
    public function searchableAs(): string;

    public function searchableUsing(): Engine;

    /**
     * @return array<mixed>
     */
    public function toSearchableArray(): array;
}
