<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes;

use Attribute;

#[Attribute]
class ScoutSearchableSettings
{
    public function __construct(
        public array $searchable = [],
        public array $displayable = [],
        public array $filterable = [],
        public array $sortable = [],
        public bool $globallySearchable = false,
    ) {
        //
    }
}
