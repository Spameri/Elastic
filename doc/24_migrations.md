# ElasticSearch Migrations

This guide covers how to perform data and schema migrations in ElasticSearch using the Spameri/Elastic library.

---

## Overview

### What is an ElasticSearch Migration?

A migration is any change to your ElasticSearch index structure or data transformation. Unlike SQL databases, ElasticSearch has significant limitations:

| SQL Database | ElasticSearch |
|--------------|---------------|
| `ALTER TABLE ADD COLUMN` | ✅ Supported via PutMapping |
| `ALTER TABLE MODIFY COLUMN` | ❌ Not supported - requires reindex |
| `ALTER TABLE DROP COLUMN` | ⚠️ Field remains, just unused |
| `ALTER TABLE RENAME COLUMN` | ❌ Not supported - requires reindex |

**Key Limitation:** Once a field is created with a type, you cannot change that type. You must create a new index with the correct mapping and migrate the data.

### When Do You Need a Migration?

| Change | Migration Required | Strategy |
|--------|-------------------|----------|
| Add new field | No downtime | PutMapping |
| Change field type | Reindex required | Zero-downtime or Dump/Restore |
| Remove field | No migration | Just stop using it |
| Rename field | Reindex required | Zero-downtime or Dump/Restore |
| Change analyzer | Reindex required | Zero-downtime or Dump/Restore |
| Add nested object | Depends | PutMapping if new, reindex if modifying |

---

## Migration Strategies

### Strategy 1: Add New Fields (No Downtime)

**When to use:**
- Adding completely new fields
- Adding new nested objects
- No changes to existing fields

**Downtime:** None

Use `PutMapping` to add new fields to an existing index without reindexing.

#### CLI Approach

There is no CLI command for PutMapping. Use the programmatic approach.

#### Programmatic Approach

```php
<?php declare(strict_types = 1);

namespace App\Migration;

class AddDescriptionField
{
    public function __construct(
        private readonly \Spameri\Elastic\Model\Indices\PutMapping $putMapping,
    ) {}

    public function execute(): void
    {
        $mapping = new \Spameri\ElasticQuery\Mapping\Mappings();

        // Add new text field
        $mapping->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'description',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
            )
        );

        // Add new keyword field
        $mapping->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'category',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        );

        $this->putMapping->execute('video', $mapping);
    }
}
```

#### Adding Nested Objects

```php
public function addNestedObject(): void
{
    $mapping = new \Spameri\ElasticQuery\Mapping\Mappings();

    // Add nested object with multiple fields
    $metadata = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
        'metadata',
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'author',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            ),
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'createdAt',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_DATE
            )
        )
    );
    $mapping->addMappingFieldObject($metadata);

    $this->putMapping->execute('video', $mapping);
}
```

---

### Strategy 2: Dump & Restore (Brief Downtime)

**When to use:**
- Development environments
- Small datasets (< 100,000 documents)
- Acceptable brief downtime
- Simple mapping changes

**Downtime:** Minutes to hours depending on data size

#### CLI Workflow

```bash
# Step 1: Backup existing data
php bin/console spameri:elastic:dump-index video
# Creates: video_dump.json

# Step 2: Delete and recreate index with new mapping
php bin/console spameri:elastic:initialize-index -f video

# Step 3: Restore data
php bin/console spameri:elastic:load-dump video_dump.json

# Optional: Specify batch size for large datasets
php bin/console spameri:elastic:load-dump video_dump.json --step=1000
```

#### Programmatic Approach

```php
<?php declare(strict_types = 1);

namespace App\Migration;

class DumpRestoreMigration
{
    public function __construct(
        private readonly \Spameri\Elastic\Model\DumpIndex $dumpIndex,
        private readonly \Spameri\Elastic\Model\RestoreIndex $restoreIndex,
        private readonly \Spameri\Elastic\Model\DeleteIndex $deleteIndex,
        private readonly \Spameri\Elastic\Model\CreateIndex $createIndex,
        private readonly \App\Model\Settings\VideoMapping $videoMapping,
    ) {}

    public function execute(): void
    {
        $dumpFile = '/tmp/video_migration_' . date('Y-m-d-H-i-s') . '.json';

        // Step 1: Dump
        $this->dumpIndex->execute('video', $dumpFile);

        // Step 2: Delete old index
        $this->deleteIndex->execute('video');

        // Step 3: Create with new mapping
        $this->createIndex->execute('video', $this->videoMapping);

        // Step 4: Restore (500 documents per batch)
        $this->restoreIndex->execute($dumpFile, 500);

        // Step 5: Cleanup
        unlink($dumpFile);
    }
}
```

---

### Strategy 3: Zero-Downtime Reindexing (Production)

**When to use:**
- Production environments
- Large datasets
- Cannot afford any downtime
- Critical applications

**Downtime:** None (atomic alias switch)

This strategy uses index aliases to switch between index versions atomically.

#### Concept

```
Before migration:
    video (alias) → video_v1 (index)

During migration:
    video (alias) → video_v1 (index)
    video_v2 (index) ← importing data

After migration:
    video (alias) → video_v2 (index)
    video_v1 (index) ← kept for rollback
```

#### CLI Workflow

```bash
# Step 1: Create new index version with updated mapping
# First, update your IndexConfigInterface to use versioned name
php bin/console spameri:elastic:create-index video_v2

# Step 2: Import data to new index
# Use your import system to populate video_v2
# (See Integration with Import System section below)

# Step 3: Verify new index has correct data
# Run your verification queries against video_v2

# Step 4: Switch alias atomically
php bin/console spameri:elastic:remove-alias video_v1 video
php bin/console spameri:elastic:add-alias video_v2 video

# Step 5: Keep old index for rollback (optional cleanup later)
# After verification period:
php bin/console spameri:elastic:delete-index video_v1
```

#### Programmatic Approach with MoveAlias

For atomic alias switching, use `MoveAlias`:

```php
<?php declare(strict_types = 1);

namespace App\Migration;

class ZeroDowntimeMigration
{
    public function __construct(
        private readonly \Spameri\Elastic\Model\CreateIndex $createIndex,
        private readonly \Spameri\Elastic\Model\Indices\MoveAlias $moveAlias,
        private readonly \Spameri\Elastic\Model\Indices\Exists $indexExists,
        private readonly \App\Model\Settings\VideoMapping $videoMapping,
        private readonly \App\Import\VideoDataProvider $dataProvider,
        private readonly \Spameri\Elastic\Import\Run $importRun,
    ) {}

    public function execute(string $currentVersion, string $newVersion): void
    {
        $aliasName = 'video';
        $oldIndex = "video_{$currentVersion}";
        $newIndex = "video_{$newVersion}";

        // Step 1: Verify old index exists
        if (!$this->indexExists->execute($oldIndex)) {
            throw new \RuntimeException("Old index {$oldIndex} does not exist");
        }

        // Step 2: Create new index with updated mapping
        $this->createIndex->execute($newIndex, $this->videoMapping);

        // Step 3: Import data to new index
        // Configure your import to target the new index
        $this->importRun->execute(
            $this->dataProvider,
            new \App\Import\VideoDataPrepare($newIndex),
            new \App\Import\VideoDataImport(),
        );

        // Step 4: Atomic alias switch
        $this->moveAlias->execute($aliasName, $oldIndex, $newIndex);

        // Old index is kept for rollback - delete manually when verified
    }
}
```

#### Important: Configure Entity to Use Alias

Ensure your `IndexConfigInterface` returns the alias name, not the versioned index name:

```php
class VideoMapping implements \Spameri\Elastic\Settings\IndexConfigInterface
{
    public function indexName(): string
    {
        // Return alias name - queries go here
        return 'video';
    }

    // For creating versioned indexes, use a separate method or parameter
    public function versionedIndexName(string $version): string
    {
        return "video_{$version}";
    }
}
```

---

### Strategy 4: Blue-Green Deployment

**When to use:**
- Very large datasets
- Need to verify new index extensively before switching
- Want gradual traffic migration

**Downtime:** None

This extends zero-downtime reindexing with gradual traffic shifting.

#### Implementation

```php
<?php declare(strict_types = 1);

namespace App\Migration;

class BlueGreenMigration
{
    private string $activeIndex = 'video_v1';
    private string $newIndex = 'video_v2';
    private int $newIndexPercentage = 0;

    public function __construct(
        private readonly \Spameri\Elastic\Model\GetBy $getBy,
    ) {}

    /**
     * Route queries to appropriate index based on percentage
     */
    public function routeQuery(\Spameri\ElasticQuery\ElasticQuery $query): string
    {
        // Gradually increase traffic to new index
        if (random_int(1, 100) <= $this->newIndexPercentage) {
            return $this->newIndex;
        }
        return $this->activeIndex;
    }

    /**
     * Increase percentage of traffic to new index
     */
    public function increaseNewIndexTraffic(int $percentage): void
    {
        $this->newIndexPercentage = min(100, max(0, $percentage));
    }

    /**
     * Complete migration - switch fully to new index
     */
    public function completeMigration(): void
    {
        $this->newIndexPercentage = 100;
        $this->activeIndex = $this->newIndex;
    }
}
```

---

## Common Migration Scenarios

### Adding a New Field

**Scenario:** Add a `rating` field to existing videos.

```php
// 1. Update your entity
class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $title,
        private ?float $rating = null, // New field with default
    ) {}
}

// 2. Update mapping
$mapping = new \Spameri\ElasticQuery\Mapping\Mappings();
$mapping->addMappingField(
    new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
        'rating',
        \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_FLOAT
    )
);
$putMapping->execute('video', $mapping);

// 3. Existing documents will have null for rating
// New documents will include rating field
```

### Changing Field Type

**Scenario:** Change `year` from `text` to `integer`.

**This requires reindexing.** Use Strategy 2 or 3.

```php
// 1. Update IndexConfigInterface
public function provide(): \Spameri\ElasticQuery\Mapping\Settings
{
    $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

    // Changed from TYPE_TEXT to TYPE_INTEGER
    $settings->addMappingField(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'year',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_INTEGER
        )
    );

    return $settings;
}

// 2. Run dump/restore migration
// php bin/console spameri:elastic:dump-index video
// php bin/console spameri:elastic:initialize-index -f video
// php bin/console spameri:elastic:load-dump video_dump.json
```

### Removing a Field

**Scenario:** Remove deprecated `legacyId` field.

ElasticSearch does not support removing fields from mappings. Options:

1. **Ignore it:** Field remains in mapping but is unused
2. **Reindex:** Create new index without the field

```php
// Option 1: Just remove from entity and mapping config
// Old documents keep the field, new documents won't have it
// Queries still work, field is just ignored

// Option 2: Reindex to clean up storage
// Use Strategy 2 or 3 with mapping that excludes the field
```

### Renaming a Field

**Scenario:** Rename `name` to `title`.

**This requires reindexing with data transformation.**

```php
<?php declare(strict_types = 1);

namespace App\Migration;

class RenameFieldMigration
{
    public function __construct(
        private readonly \Spameri\Elastic\Model\DumpIndex $dumpIndex,
        private readonly \Spameri\Elastic\Model\RestoreIndex $restoreIndex,
        private readonly \Spameri\Elastic\Model\DeleteIndex $deleteIndex,
        private readonly \Spameri\Elastic\Model\CreateIndex $createIndex,
        private readonly \App\Model\Settings\VideoMapping $videoMapping,
    ) {}

    public function execute(): void
    {
        $dumpFile = '/tmp/video_rename_migration.json';

        // Step 1: Dump
        $this->dumpIndex->execute('video', $dumpFile);

        // Step 2: Transform data - rename field in dump file
        $this->transformDumpFile($dumpFile);

        // Step 3: Delete and recreate
        $this->deleteIndex->execute('video');
        $this->createIndex->execute('video', $this->videoMapping);

        // Step 4: Restore
        $this->restoreIndex->execute($dumpFile, 500);
    }

    private function transformDumpFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $transformed = [];

        foreach ($lines as $line) {
            if (empty($line)) continue;

            $data = json_decode($line, true);

            // Skip action lines ({"index":...})
            if (isset($data['index'])) {
                $transformed[] = $line;
                continue;
            }

            // Transform document: rename 'name' to 'title'
            if (isset($data['name'])) {
                $data['title'] = $data['name'];
                unset($data['name']);
            }

            $transformed[] = json_encode($data);
        }

        file_put_contents($filePath, implode("\n", $transformed));
    }
}
```

### Adding Nested Object

**Scenario:** Add `technical` object with `resolution` and `codec` fields.

```php
// If adding to existing index (no documents have this field yet):
$mapping = new \Spameri\ElasticQuery\Mapping\Mappings();

$technical = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldObject(
    'technical',
    new \Spameri\ElasticQuery\Mapping\Settings\Mapping\FieldCollection(
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'resolution',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
        ),
        new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
            'codec',
            \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
        )
    )
);
$mapping->addMappingFieldObject($technical);

$putMapping->execute('video', $mapping);
```

### Changing Analyzer

**Scenario:** Change `title` field from standard analyzer to custom analyzer.

**This requires reindexing** because analyzers are applied at index time.

```php
// 1. Update IndexConfigInterface with new analyzer
public function provide(): \Spameri\ElasticQuery\Mapping\Settings
{
    $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

    // Configure custom analyzer in settings
    $settings->setAnalysis(
        new \Spameri\ElasticQuery\Mapping\Settings\Analysis(
            new \Spameri\ElasticQuery\Mapping\Settings\Analysis\Analyzer\AnalyzerCollection(
                new \Spameri\ElasticQuery\Mapping\Settings\Analysis\Analyzer\Custom(
                    'my_analyzer',
                    'standard',
                    new \Spameri\ElasticQuery\Mapping\Settings\Analysis\TokenFilterCollection(
                        new \Spameri\ElasticQuery\Mapping\Settings\Analysis\TokenFilter\Lowercase('lowercase')
                    )
                )
            )
        )
    );

    // Use custom analyzer for title field
    $title = new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
        'title',
        \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
    );
    $title->setAnalyzer('my_analyzer');
    $settings->addMappingField($title);

    return $settings;
}

// 2. Reindex using Strategy 2 or 3
```

---

## Programmatic Migrations

### Creating a Migration Class

```php
<?php declare(strict_types = 1);

namespace App\Migration;

abstract class AbstractMigration
{
    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function up(): void;
    abstract public function down(): void; // Rollback

    protected function log(string $message): void
    {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    }
}
```

### Example Migration Implementation

```php
<?php declare(strict_types = 1);

namespace App\Migration;

class Migration20241127AddRatingField extends AbstractMigration
{
    public function __construct(
        private readonly \Spameri\Elastic\Model\Indices\PutMapping $putMapping,
        private readonly \Spameri\Elastic\Model\Indices\GetMapping $getMapping,
    ) {}

    public function getName(): string
    {
        return '20241127_add_rating_field';
    }

    public function getDescription(): string
    {
        return 'Adds rating field (float) to video index';
    }

    public function up(): void
    {
        $this->log('Adding rating field to video index...');

        $mapping = new \Spameri\ElasticQuery\Mapping\Mappings();
        $mapping->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'rating',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_FLOAT
            )
        );

        $this->putMapping->execute('video', $mapping);

        $this->log('Rating field added successfully');
    }

    public function down(): void
    {
        // Cannot remove field from ElasticSearch mapping
        // Field will remain but be unused
        $this->log('Rollback: rating field cannot be removed from mapping');
        $this->log('Field will remain but should not be used');
    }
}
```

### Integration with Import System

For large data migrations, use the Import system:

```php
<?php declare(strict_types = 1);

namespace App\Import;

class VideoMigrationDataProvider implements \Spameri\Elastic\Import\DataProviderInterface
{
    public function __construct(
        private readonly \Spameri\Elastic\Model\Scroll $scroll,
    ) {}

    public function provide(): \Generator
    {
        // Use Scroll API to iterate through all documents
        $query = new \Spameri\ElasticQuery\ElasticQuery();
        $query->options()->setSize(1000);
        $query->options()->changeScrollId('5m');

        $result = $this->scroll->execute($query, 'video_old');

        while ($result->hits()->count() > 0) {
            foreach ($result->hits() as $hit) {
                yield $hit->source();
            }

            $query->options()->changeScrollId($result->scrollId());
            $result = $this->scroll->execute($query, 'video_old');
        }

        $this->scroll->closeScroll($result->scrollId());
    }

    public function count(): int
    {
        // Return approximate count for progress bar
        return 100000;
    }
}
```

```php
<?php declare(strict_types = 1);

namespace App\Import;

class VideoMigrationDataPrepare implements \Spameri\Elastic\Import\PrepareImportDataInterface
{
    public function prepare(mixed $item): \Spameri\Elastic\Import\ResponseInterface
    {
        // Transform data during migration
        $data = $item;

        // Example: rename field
        if (isset($data['name'])) {
            $data['title'] = $data['name'];
            unset($data['name']);
        }

        // Example: add default value
        $data['rating'] = $data['rating'] ?? 0.0;

        return new \Spameri\Elastic\Import\Response\SimpleResponse($data);
    }
}
```

---

## Rollback Strategies

### Instant Rollback with Aliases

If using zero-downtime migration, rollback is simple:

```php
// Migration failed or issues found
// Switch alias back to old index

$moveAlias->execute('video', 'video_v2', 'video_v1');

// Now queries go to old index immediately
// New index can be investigated or deleted
```

```bash
# CLI rollback
php bin/console spameri:elastic:remove-alias video_v2 video
php bin/console spameri:elastic:add-alias video_v1 video
```

### Keep Backup Indexes

Best practice: Keep old index for a verification period:

```php
class MigrationManager
{
    private const ROLLBACK_PERIOD_DAYS = 7;

    public function scheduledCleanup(): void
    {
        // Find indexes older than rollback period
        $oldIndexes = $this->findOldIndexes();

        foreach ($oldIndexes as $index) {
            if ($this->isOlderThan($index, self::ROLLBACK_PERIOD_DAYS)) {
                $this->deleteIndex->execute($index);
                $this->log("Deleted old index: {$index}");
            }
        }
    }
}
```

### Dump Before Migration

Always create a dump before destructive migrations:

```php
public function migrateWithBackup(): void
{
    $backupFile = sprintf(
        '/backups/video_%s_pre_migration.json',
        date('Y-m-d-H-i-s')
    );

    // Backup
    $this->dumpIndex->execute('video', $backupFile);
    $this->log("Backup created: {$backupFile}");

    try {
        // Run migration
        $this->runMigration();
    } catch (\Throwable $e) {
        $this->log("Migration failed: {$e->getMessage()}");
        $this->log("Restore from: {$backupFile}");
        throw $e;
    }
}
```

---

## Best Practices

### 1. Always Backup Before Migration

```bash
# Before any destructive operation
php bin/console spameri:elastic:dump-index video
```

### 2. Test on Staging First

Never run migrations directly on production. Test the complete workflow:
1. Clone production data to staging
2. Run migration on staging
3. Verify data integrity
4. Test application functionality
5. Only then run on production

### 3. Monitor During Migration

```php
public function migrateWithMonitoring(): void
{
    $startTime = microtime(true);
    $startCount = $this->getDocumentCount('video');

    $this->runMigration();

    $endTime = microtime(true);
    $endCount = $this->getDocumentCount('video');

    $this->log(sprintf(
        'Migration completed in %.2f seconds. Documents: %d → %d',
        $endTime - $startTime,
        $startCount,
        $endCount
    ));

    if ($endCount < $startCount * 0.99) {
        $this->alert('Document count decreased by more than 1%!');
    }
}
```

### 4. Verify Data Integrity

```php
public function verifyMigration(): bool
{
    // Check document count
    $oldCount = $this->getDocumentCount('video_v1');
    $newCount = $this->getDocumentCount('video_v2');

    if ($oldCount !== $newCount) {
        $this->log("Count mismatch: {$oldCount} vs {$newCount}");
        return false;
    }

    // Sample verification
    $samples = $this->getSampleDocuments('video_v1', 100);
    foreach ($samples as $sample) {
        $newDoc = $this->getDocument('video_v2', $sample['_id']);
        if (!$this->documentsMatch($sample, $newDoc)) {
            $this->log("Document mismatch: {$sample['_id']}");
            return false;
        }
    }

    return true;
}
```

### 5. Use Batch Sizes Appropriate for Your Data

```bash
# Small documents (< 1KB): larger batches
php bin/console spameri:elastic:load-dump dump.json --step=2000

# Large documents (> 10KB): smaller batches
php bin/console spameri:elastic:load-dump dump.json --step=100

# Very large documents or limited memory
php bin/console spameri:elastic:load-dump dump.json --step=50
```

### 6. Plan for Failures

```php
class ResilientMigration
{
    public function execute(): void
    {
        $checkpoint = $this->loadCheckpoint();

        try {
            foreach ($this->getDocuments() as $index => $doc) {
                if ($index < $checkpoint) {
                    continue; // Skip already processed
                }

                $this->processDocument($doc);

                if ($index % 1000 === 0) {
                    $this->saveCheckpoint($index);
                }
            }
        } catch (\Throwable $e) {
            $this->saveCheckpoint($index);
            throw $e;
        }
    }
}
```

---

## Troubleshooting

### Error: "mapper [field] cannot be changed from type [text] to [integer]"

**Cause:** Attempting to change field type via PutMapping.

**Solution:** Field types cannot be changed. You must reindex:
```bash
php bin/console spameri:elastic:dump-index video
php bin/console spameri:elastic:initialize-index -f video
php bin/console spameri:elastic:load-dump video_dump.json
```

### Error: "index_not_found_exception"

**Cause:** Trying to operate on non-existent index.

**Solution:** Verify index exists before migration:
```php
if (!$this->indexExists->execute('video')) {
    throw new \RuntimeException('Index does not exist');
}
```

### Error: "Bulk indexing failed"

**Cause:** Documents don't match new mapping.

**Solution:**
1. Check which documents failed in the response
2. Transform data to match new mapping before restore
3. Use smaller batch sizes to isolate failures

### Migration is Very Slow

**Causes and solutions:**
1. **Batch size too small:** Increase `--step` parameter
2. **Batch size too large:** Decrease if getting memory errors
3. **No connection pooling:** Ensure HTTP keep-alive is enabled
4. **Index refresh after each document:** Use bulk operations with `InsertMultiple`

### Data Loss After Migration

**Prevention:**
1. Always dump before destructive operations
2. Verify document counts before and after
3. Keep old index until verification complete
4. Use zero-downtime migration in production

**Recovery:**
```bash
# If you have a dump
php bin/console spameri:elastic:initialize-index -f video
php bin/console spameri:elastic:load-dump video_dump.json

# If using aliases
php bin/console spameri:elastic:remove-alias video_v2 video
php bin/console spameri:elastic:add-alias video_v1 video
```

---

## Next Steps

- [Console Commands](23_console_commands.md) - CLI reference for migration commands
- [Index Mapping](05_new_index_with_mapping.md) - Creating index configurations
- [Import System](15_import_system.md) - Bulk data imports
- [Model Services](14_model_services.md) - Low-level index operations
