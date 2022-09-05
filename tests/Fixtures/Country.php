<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Laravel\Scout\EngineManager;

class Country extends Model
{
    use Searchable;

    /**
     * The attributes that should be visible in serialization.
     *
     * @var string[]
     */
    protected $visible = ['name', 'iso'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * Get the engine used to index the model.
     *
     * @return \Laravel\Scout\Engines\Engine
     */
    public function searchableUsing()
    {
        return app(EngineManager::class)->engine('collection');
    }
}
