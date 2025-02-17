---
description: Model attribute or methods used to setup other index settings for Meilisearch
---

# Index settings

Laravel Scout only lets you setup the searchable attributes but additional configuration is available at every index sent.

{% hint style="info" %}
Remember to check [the official documentation about these filters and sorts](https://docs.meilisearch.com/learn/getting_started/filtering_and_sorting.html).
{% endhint %}

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Tag extends Model
{
    use Searchable;

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
    
    /**
     * Get the search displayable attributes array for the model.
     *
     * @return array<string>
     */
    public function searchDisplayableAttributes(): array
    {
        return ['name'];
    }

    /**
     * Get the search filterable attributes array for the model.
     *
     * @return array<string>
     */
    public function searchableFilters(): array
    {
        return ['name'];
    }

    /**
     * Get the search sortable attributes array for the model.
     *
     * @return array<string>
     */
    public function searchableSorts(): array
    {
        return ['slug'];
    }
}
```

### Using PHP attribute

Everything and more can be also setup on the PHP attribute dedicated to this:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

#[ScoutSearchableSettings(searchable: ['name', 'slug', displayable: ['name'], sortable: ['slug'], globallySearchable: true])]
class Tag extends Model
{
    use Searchable;
    
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
```
