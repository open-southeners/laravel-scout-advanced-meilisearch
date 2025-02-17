---
description: >-
  This package adds more commands to Laravel to manage Meilisearch search engine
  stuff
---

# Commands

### Attributes index settings

For sending filterable, sortable and displayable attributes to your Meilisearch server, configure your already searchable models using the methods or the PHP attribute from [Index settings](index-settings.md).

Then run the following command:

```bash
php artisan scout:update "App\Models\User"
```

You could also run this command with `--wait` option which tells the command to wait for the task to finish:

```bash
php artisan scout:update "App\Models\User" --wait
```

### Dumps

Create Meilisearch data dumps (data backups that will be saved on your Meilisearch server), with the following command:

```bash
php artisan scout:dump
```

As `scout:update` command, this also have a `--wait` option:

```bash
php artisan scout:dump --wait
```

[Read more about Meilisearch dumps here](https://docs.meilisearch.com/learn/advanced/dumps.html).

### Tasks

List all tasks via command line, just running the following:

```bash
php artisan scout:tasks
```

**They can be even filtered!** (see more options running it with `--help`)

```bash
php artisan scout:tasks --status=succeeded
```

#### Canceling enqueued tasks

Also can cancel tasks with a very simple command, you can either cancel an specific task or multiple:

```bash
php artisan scout:tasks-cancel 1
```

The previous command will cancel task with UID = 1. If you wish to cancel multiple you could send them separated by comma or using options like:

```bash
php artisan scout:tasks-cancel --before-enqueued=1d
```

So this will cancel all tasks that were enqueued before 1 day (can also send 1m, 1y... **as in the background this is using `Carbon::now()->add()` & `Carbon::now()->sub()` methods**)

#### Prune finished tasks

As canceling tasks won't make them disappear from the tasks history, you can just run the following:

```bash
php artisan scout:tasks-prune
```

Just for safety for debug purposes, **this command does not remove those tasks that failed**, if you wish to do so, run the command with `--include-failed` like so:

```bash
php artisan scout:tasks-prune --include-failed
```

**Don't worry, this will not remove tasks that were enqueued and not finished, as stated by Meilisearch official docs (see link just below).**

[Read more about Meilisearch tasks here](https://docs.meilisearch.com/learn/advanced/asynchronous_operations.html#task-workflow).
