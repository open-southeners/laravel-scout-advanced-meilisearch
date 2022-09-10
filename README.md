# Laravel Scout Advanced Meilisearch

Advanced Meilisearch integration with Laravel Scout.

## Status

[![latest tag](https://img.shields.io/github/v/tag/open-southeners/laravel-scout-advaced-meilisearch?label=latest&sort=semver)](https://github.com/open-southeners/laravel-scout-advaced-meilisearch/releases/latest) [![packagist version](https://img.shields.io/packagist/v/open-southeners/laravel-scout-advaced-meilisearch)](https://packagist.org/packages/open-southeners/laravel-scout-advaced-meilisearch) [![required php version](https://img.shields.io/packagist/php-v/open-southeners/laravel-scout-advaced-meilisearch)](https://www.php.net/supported-versions.php) [![run-tests](https://github.com/open-southeners/laravel-scout-advaced-meilisearch/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/open-southeners/laravel-scout-advaced-meilisearch/actions/workflows/tests.yml) [![phpstan](https://github.com/open-southeners/laravel-scout-advaced-meilisearch/actions/workflows/phpstan.yml/badge.svg)](https://github.com/open-southeners/laravel-scout-advaced-meilisearch/actions/workflows/phpstan.yml) [![Laravel Pint](https://img.shields.io/badge/code%20style-pint-orange?logo=laravel)](https://github.com/open-southeners/laravel-scout-advaced-meilisearch/actions/workflows/pint.yml) [![Codacy Badge](https://app.codacy.com/project/badge/Grade/63c83ba040444c1197fcf09c091b995a)](https://www.codacy.com/gh/open-southeners/laravel-scout-advaced-meilisearch/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=open-southeners/laravel-scout-advaced-meilisearch&amp;utm_campaign=Badge_Grade) [![Codacy Badge](https://app.codacy.com/project/badge/Coverage/63c83ba040444c1197fcf09c091b995a)](https://www.codacy.com/gh/open-southeners/laravel-scout-advaced-meilisearch/dashboard?utm_source=github.com&utm_medium=referral&utm_content=open-southeners/laravel-scout-advaced-meilisearch&utm_campaign=Badge_Coverage) [![Edit on VSCode online](https://img.shields.io/badge/vscode-edit%20online-blue?logo=visualstudiocode)](https://vscode.dev/github/open-southeners/laravel-scout-advaced-meilisearch)

## Getting started

Install the package using Composer:

```
composer require open-southeners/laravel-scout-advaced-meilisearch
```

### Filterable and sortable attributes

For sending filterable and sortable attributes to your Meilisearch server, configure your already searchable models like so:

```php
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
     * Get the search sortable attributes array for the model.
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

**In case your project is using PHP 8**, you can do this by attributes on the model class or the `toSearchableArray` method:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Scout\Searchable;
use OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes;

#[ScoutSearchableAttributes(filterable: ['email'], sortable: ['name'])]
class User extends Authenticatable
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
            'email' => $this->email,
        ];
    }
}
```

And finally run the following artisan command:

```bash
php artisan scout:update "App\Models\User"
```

You could also run this command with `--wait` option which tells the command to wait for the task to finish:

```bash
php artisan scout:update "App\Models\User" --wait
```

Remember to check [the official documentation about these filters and sorts](https://docs.meilisearch.com/learn/getting_started/filtering_and_sorting.html).

### Dumps

From v1.1 now you can also create Meilisearch data dumps (data backups that will be saved on your Meilisearch server), with the following command:

```bash
php artisan scout:dump
```

As `scout:update` command, this also have a `--wait` option:

```bash
php artisan scout:dump --wait
```

[Read more about Meilisearch dumps here](https://docs.meilisearch.com/learn/advanced/dumps.html).

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
