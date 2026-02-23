# Console Commands Reference

This document provides a complete reference for all CLI commands available in Spameri/Elastic.

## Prerequisites

Console commands require a Symfony Console implementation. Add to your configuration:

```neon
extensions:
    console: Contributte\Console\DI\ConsoleExtension
```

---

## Index Management Commands

### spameri:elastic:initialize-index

Creates indexes and initializes them with settings and mappings from your `IndexConfigInterface` implementations.

```bash
php bin/console spameri:elastic:initialize-index [options] [--] [<entityName>...]
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `entityName` | Optional. One or more index names to initialize. If omitted, all configured indexes are initialized. |

**Options:**

| Option | Short | Description |
|--------|-------|-------------|
| `--force` | `-f` | Delete existing index before creating. **Warning: This deletes all data!** |

**Examples:**

```bash
# Initialize all indexes
php bin/console spameri:elastic:initialize-index

# Initialize specific index
php bin/console spameri:elastic:initialize-index video

# Initialize multiple indexes
php bin/console spameri:elastic:initialize-index video person

# Force recreate (delete and create fresh)
php bin/console spameri:elastic:initialize-index -f video
```

**Behavior:**

1. Locates `IndexConfigInterface` implementations for specified indexes
2. If `-f` flag: deletes existing index first
3. Creates index with mappings from `provide()` method
4. Reports success or failure for each index

---

### spameri:elastic:create-index

Creates a single index with its mapping configuration.

```bash
php bin/console spameri:elastic:create-index <indexName>
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `indexName` | **Required.** Name of the index to create. |

**Example:**

```bash
php bin/console spameri:elastic:create-index video
```

**Notes:**

- Fails if index already exists (use `initialize-indexes -f` to recreate)
- Requires corresponding `IndexConfigInterface` implementation registered in DI

---

### spameri:elastic:delete-index

Deletes an index and all its data.

```bash
php bin/console spameri:elastic:delete-index <indexName>
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `indexName` | **Required.** Name of the index to delete. |

**Example:**

```bash
php bin/console spameri:elastic:delete-index video
```

**Warning:** This permanently deletes all data in the index. There is no undo.

---

## Data Migration Commands

### spameri:elastic:dump-index

Exports all documents from an index to a JSON file for backup or migration.

```bash
php bin/console spameri:elastic:dump-index <indexName>
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `indexName` | **Required.** Name of the index to dump. |

**Output:**

Creates a file named `<indexName>_dump.json` in the current directory containing all documents in bulk format.

**Example:**

```bash
php bin/console spameri:elastic:dump-index video
# Creates: video_dump.json
```

**Use Cases:**

- Backup before mapping changes
- Migrate data between environments
- Archive index data

---

### spameri:elastic:load-dump

Imports documents from a dump file back into ElasticSearch.

```bash
php bin/console spameri:elastic:load-dump <filePath> [<step>]
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `filePath` | **Required.** Path to the dump JSON file. |
| `step` | Optional. Batch size for bulk operations. Default: 500. |

**Examples:**

```bash
# Load with default batch size
php bin/console spameri:elastic:load-dump video_dump.json

# Load with smaller batches (for large documents)
php bin/console spameri:elastic:load-dump video_dump.json 100

# Load with larger batches (faster for small documents)
php bin/console spameri:elastic:load-dump video_dump.json 1000
```

**Notes:**

- Index must exist before loading
- Large datasets benefit from adjusted step size
- Progress is displayed during import

---

## Alias Management Commands

### spameri:elastic:add-alias

Adds an alias to an index for zero-downtime reindexing and environment abstraction.

```bash
php bin/console spameri:elastic:add-alias <indexName> <aliasName>
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `indexName` | **Required.** Name of the index. |
| `aliasName` | **Required.** Alias to add. |

**Example:**

```bash
php bin/console spameri:elastic:add-alias video_v2 video
```

**Use Cases:**

- Zero-downtime reindexing: create new index, add alias, remove old
- Environment abstraction: use alias in code, point to versioned indexes
- A/B testing: switch alias between index versions

---

### spameri:elastic:remove-alias

Removes an alias from an index.

```bash
php bin/console spameri:elastic:remove-alias <indexName> <aliasName>
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `indexName` | **Required.** Name of the index. |
| `aliasName` | **Required.** Alias to remove. |

**Example:**

```bash
php bin/console spameri:elastic:remove-alias video_v1 video
```

---

## Common Workflows

### Initial Setup

```bash
# 1. Create all indexes from configuration
php bin/console spameri:elastic:initialize-index
```

### Development Reset

```bash
# Force recreate all indexes (deletes data)
php bin/console spameri:elastic:initialize-index -f
```

### Mapping Migration (Preserving Data)

```bash
# 1. Backup existing data
php bin/console spameri:elastic:dump-index video

# 2. Delete and recreate with new mapping
php bin/console spameri:elastic:initialize-index -f video

# 3. Restore data
php bin/console spameri:elastic:load-dump video_dump.json
```

### Zero-Downtime Reindexing

```bash
# 1. Create new index version
php bin/console spameri:elastic:create-index video_v2

# 2. Import data to new index (via your import system)

# 3. Switch alias
php bin/console spameri:elastic:remove-alias video_v1 video
php bin/console spameri:elastic:add-alias video_v2 video

# 4. Delete old index when ready
php bin/console spameri:elastic:delete-index video_v1
```

---

## Quick Reference

| Command | Description |
|---------|-------------|
| `spameri:elastic:initialize-index` | Create/recreate indexes from config |
| `spameri:elastic:create-index` | Create single index |
| `spameri:elastic:delete-index` | Delete index and data |
| `spameri:elastic:dump-index` | Export index to JSON |
| `spameri:elastic:load-dump` | Import from JSON dump |
| `spameri:elastic:add-alias` | Add alias to index |
| `spameri:elastic:remove-alias` | Remove alias from index |

---

## Troubleshooting

### Index already exists

```
Error: Index video already exists
```

**Solution:** Use `-f` flag to force recreate, or delete manually first:

```bash
php bin/console spameri:elastic:delete-index video
php bin/console spameri:elastic:create-index video
```

### No mapping found

```
Error: No IndexConfigInterface found for index: video
```

**Solution:** Ensure you have an `IndexConfigInterface` implementation registered:

```php
class VideoMapping implements \Spameri\Elastic\Settings\IndexConfigInterface
{
    public function indexName(): string
    {
        return 'video';
    }
    // ...
}
```

```neon
services:
    - App\Model\Settings\VideoMapping
```

### Connection refused

```
Error: Connection refused [tcp://127.0.0.1:9200]
```

**Solution:** Check ElasticSearch is running and configuration is correct:

```neon
spameriElasticSearch:
    host: 127.0.0.1
    port: 9200
```

---

## Next Steps

- [Configuration](02_configuration.md) - DI extension setup
- [Index Mapping](05_new_index_with_mapping.md) - Creating index configurations
- [Migrations](24_migrations.md) - Schema and data migrations
- [Import System](15_import_system.md) - Bulk data imports
