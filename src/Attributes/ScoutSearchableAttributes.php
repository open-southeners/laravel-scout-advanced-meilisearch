<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes;

use Attribute;

#[Attribute]
class ScoutSearchableAttributes
{
    public function __construct(
        public array $attributes = [],
        public array $filterable = [],
        public array $sortable = []
    ) {
        //
    }
}
