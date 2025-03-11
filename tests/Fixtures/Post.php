<?php

namespace OpenSoutheners\LaravelScoutAdvancedMeilisearch\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings;

class Post extends Model
{
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['title', 'slug', 'content'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = ['slug'];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    #[ScoutSearchableSettings(filterable: ['title'], sortable: ['slug'], globallySearchable: true)]
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
        ];
    }
}
