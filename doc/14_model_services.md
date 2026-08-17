# Model Services Reference

## Overview

The Model layer in Spameri/Elastic provides service classes that handle all interactions with ElasticSearch. These services are organized into several categories:

- **Entity Operations** - CRUD operations for entities
- **Bulk Operations** - Efficient batch processing
- **Search Operations** - Query and retrieval
- **Index Management** - Low-level index operations
- **Indices Operations** - Advanced index configuration

> **Note:** For standard CRUD operations, use [EntityManager](17_entity_manager.md) instead of these low-level services. The Model layer is primarily useful for bulk operations, aggregations, and index management where EntityManager doesn't provide direct methods.

---

## Entity Operations

### Insert

**Location:** `src/Model/Insert.php`

Persists a single entity to ElasticSearch. Automatically handles:
- Change detection (via Identity Map)
- STI entity class field injection
- Index refresh for immediate availability
- ID assignment for new entities

#### Method Signature

```php
public function execute(
    \Spameri\Elastic\Entity\AbstractElasticEntity $entity,
    string $index,
    bool $hasSti = false
): string
```

#### Parameters

- `$entity` - The entity to persist
- `$index` - Target index name
- `$hasSti` - Whether entity uses Single Table Inheritance

#### Returns

- `string` - The ElasticSearch document ID

#### Throws

- `\Spameri\Elastic\Exception\ElasticSearch` - On connection/request errors
- `\Spameri\Elastic\Exception\DocumentInsertFailed` - When indexing fails

#### Behavior

1. Checks Identity Map to see if entity changed (skips if unchanged)
2. Marks entity as inserted in Identity Map
3. Converts entity to array via `PrepareEntityArray`
4. Sends to ElasticSearch via index API
5. Refreshes index for immediate searchability
6. Updates entity with assigned ID
7. Marks final state in Identity Map

#### Usage Example

```php
// Via EntityManager (recommended)
$entityManager->persist($video);

// Direct usage (advanced)
$videoId = $insert->execute(
    $video,
    'videos',
    hasSti: false
);
```

**Note:** Insert automatically refreshes the index, making the document immediately searchable. For bulk operations, use `InsertMultiple` to avoid multiple refreshes.

---

### Get

**Location:** `src/Model/Get.php`

Retrieves a single document by its ElasticSearch ID. This is the fastest way to fetch a known entity.

#### Method Signature

```php
public function execute(
    \Spameri\Elastic\Entity\Property\ElasticId $id,
    string $index
): \Spameri\ElasticQuery\Response\ResultSingle
```

#### Parameters

- `$id` - The ElasticSearch document ID
- `$index` - Source index name

#### Returns

- `ResultSingle` - Contains the document source and metadata

#### Throws

- `\Spameri\Elastic\Exception\ElasticSearch` - On connection/request errors

#### Usage Example

```php
// Via EntityManager (recommended)
$video = $entityManager->find(
    \App\Entity\Video::class,
    new \Spameri\Elastic\Entity\Property\ElasticId('abc123')
);

// Direct usage (returns ResultSingle, not entity)
$result = $get->execute(
    new \Spameri\Elastic\Entity\Property\ElasticId('abc123'),
    'videos'
);
```

**Note:** The `EntityManager::find()` method wraps this and uses `EntityFactory` to convert the result to an entity object. Direct usage returns raw `ResultSingle`.

---

### GetBy

**Location:** `src/Model/GetBy.php`

Executes a search query and returns results. Unlike `GetAllBy`, this returns the raw `ResultSearch` object without converting hits to entities.

#### Method Signature

```php
public function execute(
    \Spameri\ElasticQuery\ElasticQuery $options,
    string $index
): \Spameri\ElasticQuery\Response\ResultSearch
```

#### Parameters

- `$options` - ElasticQuery object containing query, filters, aggregations
- `$index` - Target index name

#### Returns

- `ResultSearch` - Raw search results with hits, aggregations, stats

#### Throws

- `\Spameri\Elastic\Exception\ElasticSearch` - On connection/request errors

#### Usage Example

```php
$query = new \Spameri\ElasticQuery\ElasticQuery();
$query->query()->must()->add(
    new \Spameri\ElasticQuery\Query\Term('status', 'published')
);

// Direct usage
$resultSearch = $getBy->execute($query, 'videos');

foreach ($resultSearch->hits()->collection() as $hit) {
    // Process raw hit data
    $title = $hit->getValue('title');
}
```

**Note:** Use `EntityManager::findOneBy()` if you need entity objects instead of raw hits.

---

### GetAllBy

**Location:** `src/Model/GetAllBy.php`

Similar to `GetBy`, executes search queries. Use `EntityManager::findBy()` to get entity objects instead of raw hits.

**See:** doc/09_match_get.md and doc/13_advanced_get.md for query examples.

---

### Delete

**Location:** `src/Model/Delete.php`

Removes a single entity from ElasticSearch by its document ID.

#### Method Signature

```php
public function execute(
    \Spameri\Elastic\Entity\Property\ElasticId $id,
    string $index
): void
```

#### Parameters

- `$id` - The ElasticSearch document ID to delete
- `$index` - Source index name

#### Throws

- `\Spameri\Elastic\Exception\ElasticSearch` - On connection/request errors

#### Usage Example

```php
// Via EntityManager (recommended)
$entityManager->remove($video);

// Direct usage
$delete->execute(
    new \Spameri\Elastic\Entity\Property\ElasticId('abc123'),
    'videos'
);
```

**Note:** Unlike `Insert`, `Delete` does NOT automatically refresh the index. The document remains visible in search results until the next refresh cycle (typically 1 second).

---

## Bulk Operations

### InsertMultiple

**Location:** `src/Model/InsertMultiple.php`

Efficiently inserts multiple entities in a single bulk request. Much faster than multiple `Insert` calls because it:
- Uses ElasticSearch bulk API
- Performs single index refresh at the end

#### Method Signature

```php
public function execute(
    array $entities,
    string $index,
    bool $hasSti = false
): void
```

#### Parameters

- `$entities` - Array of `AbstractElasticEntity` objects to insert
- `$index` - Target index name
- `$hasSti` - Whether entities use Single Table Inheritance

#### Throws

- `\Spameri\Elastic\Exception\ElasticSearch` - On connection/request errors
- `\Spameri\Elastic\Exception\DocumentInsertFailed` - If any document fails

#### Usage Example

```php
$videos = [
    new Video(/* ... */),
    new Video(/* ... */),
    new Video(/* ... */),
];

$insertMultiple->execute($videos, 'videos');
```

**Performance Tip:** For importing large datasets, use the Import system (see doc/15_import_system.md) which includes progress tracking, locking, and error handling.

---

### DeleteMultiple

**Location:** `src/Model/DeleteMultiple.php`

Efficiently deletes multiple entities in a single bulk request.

#### Method Signature

```php
public function execute(
    array $ids,
    string $index
): void
```

#### Parameters

- `$ids` - Array of `ElasticId` objects to delete
- `$index` - Source index name

#### Throws

- `\Spameri\Elastic\Exception\ElasticSearch` - On connection/request errors

#### Usage Example

```php
$idsToDelete = [
    new \Spameri\Elastic\Entity\Property\ElasticId('id1'),
    new \Spameri\Elastic\Entity\Property\ElasticId('id2'),
    new \Spameri\Elastic\Entity\Property\ElasticId('id3'),
];

$deleteMultiple->execute($idsToDelete, 'videos');
```

---

## Search Operations

### Search

**Location:** `src/Model/Search.php`

Similar to `GetBy` but provides additional functionality for complex search scenarios.

**See:** doc/09_match_get.md for usage examples.

---

### Aggregate

**Location:** `src/Model/Aggregate.php`

Performs aggregations on data without retrieving documents. Returns aggregation results for analysis.

**See:** doc/10_aggregate.md for usage examples and aggregation types.

---

### Scroll

**Location:** `src/Model/Scroll.php`

Implements the ElasticSearch Scroll API for paginating through large result sets efficiently.

#### Method Signature

```php
public function execute(
    \Spameri\ElasticQuery\ElasticQuery $elasticQuery,
    string $index
): \Spameri\ElasticQuery\Response\ResultSearch
```

#### Usage Pattern

```php
// Initialize scroll
$query = new \Spameri\ElasticQuery\ElasticQuery();
$query->options()->setScrollId('1m'); // Keep scroll context for 1 minute

$result = $scroll->execute($query, 'videos');

// Process first batch
foreach ($result->hits()->collection() as $hit) {
    // Process hit
}

// Continue scrolling
while ($result->hits()->count() > 0) {
    $query->options()->setScrollId($result->scrollId());
    $result = $scroll->execute($query, 'videos');

    foreach ($result->hits()->collection() as $hit) {
        // Process hit
    }
}

// Clean up scroll context
$scroll->closeScroll($result->scrollId());
```

**Important:** Always call `closeScroll()` to free server resources. Scroll contexts timeout automatically but consume memory until then.

---

## Index Management Operations

### CreateIndex

**Location:** `src/Model/CreateIndex.php`

Creates a new index with mappings and settings from an IndexConfig.

#### Usage Example

```php
$createIndex->execute($videoConfig);
```

---

### DeleteIndex

**Location:** `src/Model/DeleteIndex.php`

Deletes an entire index.

#### Usage Example

```php
$deleteIndex->execute('videos');
```

**Warning:** This operation is irreversible and deletes all documents in the index.

---

### InitializeIndex

**Location:** `src/Model/InitializeIndex.php`

Creates an index if it doesn't exist. Safe to call multiple times.

#### Usage Example

```php
$initializeIndex->execute($videoConfig);
```

---

### DumpIndex

**Location:** `src/Model/DumpIndex.php`

Exports all documents from an index to a JSON file in bulk format.

#### Usage Example

```php
$dumpIndex->execute('videos', '/path/to/backup.json');
```

**See:** Console command `spameri:elastic:dump-index` for CLI usage.

---

### RestoreIndex

**Location:** `src/Model/RestoreIndex.php`

Imports documents from a bulk JSON file created by `DumpIndex`.

#### Method Signature

```php
public function execute(
    string $file,
    int $step = 500
): void
```

#### Parameters

- `$file` - Path to bulk JSON file
- `$step` - Number of documents per bulk request (default: 500)

#### Usage Example

```php
$restoreIndex->execute('/path/to/backup.json', step: 1000);
```

**See:** Console command `spameri:elastic:load-dump` for CLI usage.

---

## Indices Operations (Low-Level)

All classes in `src/Model/Indices/` namespace provide low-level access to ElasticSearch Indices API. These mirror the ElasticSearch REST API closely.

### Indices\Create

Creates an index with custom mappings and settings.

```php
$indices->create()->execute(
    'videos',
    [
        'settings' => [
            'number_of_shards' => 3,
            'number_of_replicas' => 1,
        ],
        'mappings' => [
            'properties' => [
                'title' => ['type' => 'text'],
                'year' => ['type' => 'integer'],
            ],
        ],
    ]
);
```

---

### Indices\Delete

Deletes an index.

```php
$indices->delete()->execute('videos');
```

---

### Indices\Exists

Checks if an index exists.

```php
$exists = $indices->exists()->execute('videos');

if ($exists) {
    // Index exists
}
```

---

### Indices\Get

Retrieves index information (settings, mappings, aliases).

```php
$indexInfo = $indices->get()->execute('videos');
```

---

### Indices\GetMapping

Gets the mapping definition for an index.

```php
$mapping = $indices->getMapping()->execute('videos');
```

---

### Indices\GetFieldMapping

Gets mapping for specific fields.

```php
$fieldMapping = $indices->getFieldMapping()->execute(
    'videos',
    ['title', 'description']
);
```

---

### Indices\PutMapping

Updates the mapping of an existing index.

```php
$indices->putMapping()->execute(
    'videos',
    [
        'properties' => [
            'new_field' => ['type' => 'keyword'],
        ],
    ],
    dynamic: 'strict' // 'true', 'false', or 'strict'
);
```

**Parameters:**
- `$index` - Index name
- `$mapping` - Mapping definition array
- `$dynamic` - Dynamic mapping mode (default: 'false')
  - `'true'` - Automatically add new fields
  - `'false'` - Ignore new fields
  - `'strict'` - Reject documents with unmapped fields

**Note:** You cannot change existing field types. You must reindex data to change mappings.

---

### Indices\PutSettings

Updates index settings. Some settings require the index to be closed first.

```php
// Settings that can be updated on open index
$indices->putSettings()->execute(
    'videos',
    [
        'index' => [
            'number_of_replicas' => 2,
        ],
    ]
);

// Settings requiring closed index
$indices->close()->execute('videos');
$indices->putSettings()->execute(
    'videos',
    [
        'index' => [
            'number_of_shards' => 5, // Requires reindex, shown for reference
        ],
    ]
);
$indices->open()->execute('videos');
```

---

### Indices\AddAlias

Adds an alias to an index.

```php
$indices->addAlias()->execute('videos_v2', 'videos');
```

**Use Case:** Enables zero-downtime reindexing. Point alias to new index version when ready.

---

### Indices\RemoveAlias

Removes an alias from an index.

```php
$indices->removeAlias()->execute('videos_v1', 'videos');
```

---

### Indices\MoveAlias

Atomically moves an alias from one index to another. This is crucial for zero-downtime deployments.

```php
$indices->moveAlias()->execute(
    alias: 'videos',
    removeFrom: 'videos_v1',
    addTo: 'videos_v2'
);
```

**Workflow Example:**
```php
// 1. Create new index version
$indices->create()->execute('videos_v2', $mapping);

// 2. Populate new index
$restoreIndex->execute('backup.json');

// 3. Atomically switch alias
$indices->moveAlias()->execute(
    alias: 'videos',
    removeFrom: 'videos_v1',
    addTo: 'videos_v2'
);

// 4. Verify and delete old index
$indices->delete()->execute('videos_v1');
```

---

### Indices\Open

Opens a closed index, making it available for read/write operations.

```php
$indices->open()->execute('videos');
```

---

### Indices\Close

Closes an index, preventing read/write operations but preserving data. Closed indices consume less resources.

```php
$indices->close()->execute('videos');
```

**Use Case:** Close old indices you want to keep but rarely access.

---

## Utility Services

### EntitySettingsLocator

**Location:** `src/Model/EntitySettingsLocator.php`

Maps entity class names to their corresponding `IndexConfigInterface` implementations. Used internally by EntityManager to determine which index to query/persist.

Auto-configured via DI - discovers all `IndexConfigInterface` implementations automatically.

---

### IdentityMap

**Location:** `src/Model/IdentityMap.php`

Tracks loaded entities to ensure each entity is instantiated only once per request. Provides:
- Entity caching by class and ID
- Change detection via serialization
- Circular reference prevention
- STI parent/child class mapping

**See:** doc/17_identity_map.md (future) for detailed documentation.

---

### ChangeSet

**Location:** `src/Model/ChangeSet.php`

Tracks whether entities are new or existing to determine which lifecycle events to fire (POST_CREATE vs POST_UPDATE).

#### Key Methods

- `markExisting($entity)` - Marks entity as loaded from database
- `isNew($entity)` - Returns true if entity was never marked existing

**Usage:** Automatically used by EventManager. No direct usage needed.

---

### VersionProvider

**Location:** `src/Model/VersionProvider.php`

Manages ElasticSearch version compatibility. Configurable via neon:

```neon
spameriElasticSearch:
    version: 8
```

---

## Best Practices

### 1. Use EntityManager for CRUD Operations

For standard CRUD, always use EntityManager:

```php
// Good - high-level, type-safe
$video = $entityManager->find(Video::class, $id);
$entityManager->persist($video);

// Avoid - low-level for application code
$result = $get->execute($id, 'videos');
```

### 2. Use Model Services for Advanced Operations

Use model services directly only for operations EntityManager doesn't support:

```php
// Aggregations - use Aggregate service
$result = $aggregate->execute($query, 'videos');

// Bulk operations - use InsertMultiple/DeleteMultiple
$insertMultiple->execute($videos, 'videos');

// Index management - use Indices services
$indices->create()->execute('videos', $mapping);
```

### 3. Use Bulk Operations for Multiple Entities

When inserting/deleting multiple entities, always use bulk operations:

```php
// Good
$insertMultiple->execute($videos, 'videos');

// Bad - N separate requests + refreshes
foreach ($videos as $video) {
    $entityManager->persist($video);
}
```

### 4. Clean Up Scroll Contexts

Always close scroll contexts to free server resources:

```php
try {
    // ... scroll processing
} finally {
    $scroll->closeScroll($scrollId);
}
```

### 5. Use Aliases for Zero-Downtime Reindexing

Never query indexes directly by version number. Always use aliases:

```php
// Good - index can be swapped without code changes
$config->indexName() // Returns 'videos' (alias)

// Bad - couples code to specific index version
$hardcodedIndex = 'videos_v2'
```

---

## Error Handling

All model services can throw:

- `\Spameri\Elastic\Exception\ElasticSearch` - Generic ElasticSearch errors
- `\Spameri\Elastic\Exception\DocumentInsertFailed` - Insert/update failures
- `\Spameri\Elastic\Exception\DocumentNotFound` - Entity not found
- `\Spameri\Elastic\Exception\IndexAlreadyExists` - Index creation conflicts
- `\Spameri\Elastic\Exception\SettingsNotLocated` - Entity config not found

Example error handling:

```php
try {
    $video = $entityManager->find(\App\Entity\Video::class, $id);

} catch (\Spameri\Elastic\Exception\DocumentNotFound $e) {
    // Handle missing entity
    return $this->error('Video not found', 404);

} catch (\Spameri\Elastic\Exception\ElasticSearch $e) {
    // Log and show generic error
    \Tracy\Debugger::log($e);
    return $this->error('Search service unavailable', 503);
}
```

---

## See Also

- [EntityManager](17_entity_manager.md) - Recommended CRUD API
- [Basic Get](08_basic_get.md) - Simple retrieval examples
- [Match Get](09_match_get.md) - Search query examples
- [Advanced Get](13_advanced_get.md) - Complex queries
- [Aggregate](10_aggregate.md) - Aggregation examples
- [Import System](15_import_system.md) - Bulk import workflows
