# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [5.1.0] - 2025-03-12

### Added

- `setModelsPath` method on `MultiSearch` class to customise the model folder path

### Fixed

- `ScoutSearchableSettings` attribute when added on the method `toSearchableArray` using `MultiSearch` (with `globallySearchable` set true) didn't get through

## [5.0.0] - 2025-03-11

### Added

- `OpenSoutheners\LaravelScoutAdvancedMeilisearch\MultiSearch` class to support [multi-search](https://www.meilisearch.com/docs/learn/multi_search/performing_federated_search)

### Changed

- **Rename `OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableAttributes` to `OpenSoutheners\LaravelScoutAdvancedMeilisearch\Attributes\ScoutSearchableSettings`**

### Removed

- **`attributes` parameter from `ScoutSearchableSettings` attribute** (already covered by Laravel Scout)
- **Laravel 9 and 10 support**
- **PHP 8.0 and 8.1 support**

## [4.1.0] - 2024-05-24

### Added

- Added support Laravel 11

## [4.0.0] - 2023-05-12

### Added

- Laravel Scout 10 support (using Meilisearch 1.x)

## [3.0.2] - 2023-02-16

### Added

- Laravel 10 support

## [3.0.1] - 2022-12-21

### Added

- Support to PHP 8.2

## [3.0.0] - 2022-12-06

### Added

- New `scout:tasks-cancel` command to cancel tasks
- New `scout:tasks-prune` command to remove succeeded and canceled tasks (can optionally include failed with option `--include-failed`)

### Changed

- This package now enforces official's Meilisearch PHP version depending on its own needs

### Removed

- Drop PHP 7.4 support

## [2.0.2] - 2022-12-06

### Fixed

- Minor fix to enforce always an status code is being returned from all commands

## [2.0.1] - 2022-09-26

### Fixed

- `scout:update` with searchable attributes replacing Meilisearch defaults `['*']`

## [2.0.0] - 2022-09-26

### Added

- New `scout:keys` command for API keys lookup on Meilisearch
- New `scout:key` command for API keys creation, modification and deletion on Meilisearch
- New `scout:tasks` command for listing tasks by status from Meilisearch (default to pending tasks list)
- Displayable & searchable attributes: https://docs.meilisearch.com/learn/configuration/displayed_searchable_attributes.html#searchable-fields

### Changed

- `scout:update` command now adding searchable & displayable fields to models (also through `ScoutSearchableAttributes` PHP attribute class)
- All command reusing code from new `MeilisearchCommand`

## [1.1.0] - 2022-09-10

### Added

- Wait option to `scout:update` command for waiting for Meilisearch task to finish
- Command `scout:dump` command for create a Meilisearch dump ([more info here](https://docs.meilisearch.com/learn/advanced/dumps.html))

## [1.0.3] - 2022-09-06

### Fixed

- Laravel service provider discovery

## [1.0.2] - 2022-09-05

### Fixed

- PHP 8 attributes not working

## [1.0.1] - 2022-09-05

### Fixed

- Command errors for `scout:update` when model isn't searchable

## [1.0.0] - 2022-09-05

### Added

- Initial release!
