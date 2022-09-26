# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
