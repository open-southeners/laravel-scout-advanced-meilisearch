<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * The attributes that should be visible in serialization.
     *
     * @var string[]
     */
    protected $visible = ['content'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];
}
