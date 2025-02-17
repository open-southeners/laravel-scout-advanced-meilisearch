---
description: Meilisearch's multi-index search support for Laravel.
---

# MultiSearch

Since Meilisearch 1.0 releases this MultiSearch feature Laravel Scout doesn't support out of the box, this feature adds support to transform multiple type of search results into its [Eloquent models](https://laravel.com/docs/eloquent) in a [Laravel Collection](https://laravel.com/docs/collections).

### Usage

To get search results in a controller see the example below.

```php
<?php

namespace App\Http\Controllers;

use OpenSoutheners\LaravelScoutAdvancedMeilisearch\MultiSearch;
use App\Models\Film;
use App\Models\Serie;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        return app(MultiSearch::class)
            ->by(Film::class, $request->q)
            ->by(Serie::class, $request->q)
            ->search();
    }
}
```

### Usage with globally searchable models

This can also be used with a single search query to be performed to all models marked as globally searchable using the [Model attribute](index-settings.md).

<pre class="language-php"><code class="lang-php">&#x3C;?php

namespace App\Models;

use Laravel\Scout\Searchable;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings;

#[ScoutSearchableSettings(searchable: ['title', 'description'], <a data-footnote-ref href="#user-content-fn-1">globallySearchable: true</a>)]
class Film extends Model
{
    use Searchable;
    
    // Model logic...
}
</code></pre>

After adding the `globallySearchable` parameter on all the relevant models the controller should now look like this:

```php
<?php

namespace App\Http\Controllers;

use OpenSoutheners\LaravelScoutAdvancedMeilisearch\MultiSearch;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        return app(MultiSearch::class)->search($request->input("q", ""));
    }
}
```

This query will be performed to all the models with the `globallySearchable` on the attribute.

[^1]: Important attribute parameter
