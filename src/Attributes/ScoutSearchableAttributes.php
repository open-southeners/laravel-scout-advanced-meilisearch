<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes;

use Attribute;

#[Attribute]
class ScoutSearchableAttributes
{
    public function __construct(
        public array $attributes = [],
        public array $filterable = [],
        public array $sortable = [],
        private array $displayable = [],
        private array $searchable = []
    ) {
        //
    }

    public function getSearchableAttributes()
    {
        if (! empty($this->attributes) && ! empty($this->displayable) && empty($this->searchable)) {
            return array_diff($this->attributes, $this->displayable);
        }

        return $this->searchable;
    }
    
    public function getDisplayableAttributes()
    {
        if (! empty($this->attributes) && ! empty($this->searchable) && empty($this->displayable)) {
            return array_diff($this->attributes, $this->searchable);
        }

        return $this->displayable;
    }
}
