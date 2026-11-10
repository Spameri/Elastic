# EntityManager Guide

## Overview

The **EntityManager** (`\Spameri\Elastic\EntityManager`) is the primary high-level interface for interacting with ElasticSearch entities. It provides a clean, ORM-like API for all CRUD operations and manages critical infrastructure like the Identity Map and event dispatching.

**Location:** `src/EntityManager.php`

**Key Features:**
- Type-safe entity operations
- Automatic Identity Map management
- Lifecycle event dispatching
- Single Table Inheritance (STI) support
- Circular reference handling

---

## When to Use EntityManager

### Use EntityManager When:
✅ Performing standard CRUD operations
✅ You want automatic Identity Map tracking
✅ You need lifecycle events (PRE_PERSIST, POST_CREATE, etc.)
✅ Working with STI entities
✅ You want clean, high-level API

### Use Model Layer Directly When:
- Aggregations (use `Aggregate` service)
- Bulk operations (use `InsertMultiple`, `DeleteMultiple`)
- Index management (create, delete, dump, restore)
- Advanced features (Scroll API, aliases, etc.)
- Direct access to `ResultSearch` objects

---

## Basic Usage

### Dependency Injection

```php
<?php

namespace App\Presenter;

class VideoPresenter
{
    public function __construct(
        private \Spameri\Elastic\EntityManager $entityManager
    ) {}

    public function actionDetail(string $id): void
    {
        $video = $this->entityManager->find(
            \App\Entity\Video::class,
            new \Spameri\Elastic\Entity\Property\ElasticId($id)
        );

        // Use $video...
    }
}
```

**Neon Configuration:**

EntityManager is auto-registered by `SpameriElasticSearchExtension`. No manual configuration needed.

---

## Core Methods

### find() - Retrieve Entity by ID

Fetches a single entity by its ElasticSearch document ID.

#### Method Signature

```php
public function find(
    string $class,
    \Spameri\Elastic\Entity\Property\ElasticIdInterface $id
): \Spameri\Elastic\Entity\ElasticEntityInterface
```

#### Parameters

- `$class` - Fully qualified entity class name
- `$id` - ElasticSearch document ID

#### Returns

- Entity instance of type `$class`

#### Throws

- `\Spameri\Elastic\Exception\DocumentNotFound` - Document doesn't exist

#### Example

```php
use Spameri\Elastic\Entity\Property\ElasticId;

// Find video by ID
$video = $this->entityManager->find(
    \App\Entity\Video::class,
    new ElasticId('abc123')
);

echo $video->title();
```

#### How It Works

1. Looks up index configuration for `Video::class` via `EntitySettingsLocator`
2. Checks Identity Map - returns cached if exists
3. Calls `Get` model service with index and ID
4. Uses `EntityFactory` to hydrate entity from Hit
5. Adds to Identity Map
6. Returns entity

**Performance:** Very fast - direct ID lookup in ElasticSearch (no query).

---

### findOneBy() - Find Single Entity by Query

Executes a search query and returns the first matching entity.

#### Method Signature

```php
public function findOneBy(
    \Spameri\ElasticQuery\ElasticQuery $query,
    string $class
): \Spameri\Elastic\Entity\ElasticEntityInterface
```

#### Parameters

- `$query` - ElasticQuery with search criteria
- `$class` - Fully qualified entity class name

#### Returns

- First matching entity of type `$class`

#### Throws

- `\Spameri\Elastic\Exception\DocumentNotFound` - No documents match query

#### Example

```php
use Spameri\ElasticQuery\ElasticQuery;
use Spameri\ElasticQuery\Query\Term;

// Find published video by slug
$query = new ElasticQuery();
$query->query()->must()->add(
    new Term('slug', 'my-video-slug')
);
$query->query()->must()->add(
    new Term('status', 'published')
);

$video = $this->entityManager->findOneBy($query, \App\Entity\Video::class);
```

#### How It Works

1. Calls `GetBy` model service
2. Takes first hit from results
3. Hydrates via `EntityFactory`
4. Adds to Identity Map
5. Returns entity

**Note:** If query matches multiple documents, only first is returned. Use `findBy()` for multiple results.

---

### findBy() - Find Multiple Entities by Query

Executes a search query and returns a collection of matching entities.

#### Method Signature

```php
public function findBy(
    \Spameri\ElasticQuery\ElasticQuery $query,
    string $class
): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
```

#### Parameters

- `$query` - ElasticQuery with search criteria
- `$class` - Fully qualified entity class name

#### Returns

- `ElasticEntityCollectionInterface` containing matched entities

#### Example

```php
use Spameri\ElasticQuery\ElasticQuery;
use Spameri\ElasticQuery\Query\Range;

// Find videos from 2020-2025
$query = new ElasticQuery();
$query->query()->must()->add(
    new Range('year', 2020, 2025)
);
$query->options()->setSize(50); // Limit results

$videos = $this->entityManager->findBy($query, \App\Entity\Video::class);

foreach ($videos as $video) {
    echo $video->title() . "\n";
}
```

#### Collection Features

The returned collection implements `\IteratorAggregate` and `\Countable`:

```php
$videos = $this->entityManager->findBy($query, \App\Entity\Video::class);

// Count results
echo count($videos); // 42

// Iterate
foreach ($videos as $video) {
    // Process each video
}

// Access underlying array
$array = $videos->collection();
```

---

### findAll() - Get All Entities

Retrieves all documents from an index without filters.

#### Method Signature

```php
public function findAll(
    string $class
): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
```

#### Parameters

- `$class` - Fully qualified entity class name

#### Returns

- Collection of all entities of type `$class`

#### Example

```php
// Get all videos (careful with large indices!)
$allVideos = $this->entityManager->findAll(\App\Entity\Video::class);

echo "Total videos: " . count($allVideos);
```

**Warning:** This fetches **all documents**. For large indices, use `findBy()` with pagination instead.

---

### persist() - Save Entity

Persists an entity to ElasticSearch. Creates new document or updates existing one.

#### Method Signature

```php
public function persist(
    \Spameri\Elastic\Entity\ElasticEntityInterface $entity
): void
```

#### Parameters

- `$entity` - Entity to save

#### Throws

- `\Spameri\Elastic\Exception\DocumentInsertFailed` - Save operation failed

#### Example: Create New Entity

```php
use Spameri\Elastic\Entity\Property\EmptyElasticId;

$video = new \App\Entity\Video(
    new EmptyElasticId(), // New entity - no ID yet
    'New Video Title',
    2025,
    // ... other properties
);

$this->entityManager->persist($video);

// Video now has ID assigned
echo $video->id()->value(); // "abc123xyz"
```

#### Example: Update Existing Entity

```php
$video = $this->entityManager->find(\App\Entity\Video::class, $id);

$video->rename(new \App\ValueObject\Title('Updated Title'));

$this->entityManager->persist($video);
```

#### How It Works

1. **PRE_PERSIST Event** - Dispatched before save (on entity and nested entities)
2. **Change Detection** - Checks if entity changed via Identity Map
3. **Insert/Update** - Calls `Insert` model service
4. **Nested Persistence** - Automatically saves nested ElasticEntityCollection items
5. **POST_PERSIST Event** - Dispatched after save
6. **POST_CREATE or POST_UPDATE Event** - Based on whether entity was new (ChangeSet tracking)
7. **Identity Map Update** - Marks entity state

#### Events Fired

- `EventManager::PRE_PERSIST` - Before any changes
- `EventManager::POST_PERSIST` - After save completes
- `EventManager::POST_CREATE` - If entity was new (EmptyElasticId)
- `EventManager::POST_UPDATE` - If entity existed (had ElasticId)

#### Cascade Behavior

When persisting an entity with `ElasticEntityCollectionInterface` properties:

```php
$person = new Person(
    new EmptyElasticId(),
    'Actor Name'
);

$video->cast->add($person); // Add to ElasticEntityCollection

$this->entityManager->persist($video);

// Both persisted:
// - Video saved to videos index
// - Person saved to people index (automatically)
```

---

### remove() - Delete Entity

Removes an entity from ElasticSearch.

#### Method Signature

```php
public function remove(
    \Spameri\Elastic\Entity\ElasticEntityInterface $entity
): void
```

#### Parameters

- `$entity` - Entity to delete

#### Throws

- `\Spameri\Elastic\Exception\ElasticSearch` - Delete operation failed

#### Example

```php
$video = $this->entityManager->find(\App\Entity\Video::class, $id);

$this->entityManager->remove($video);

// Entity deleted from ElasticSearch
```

#### How It Works

1. **PRE_DELETE Event** - Dispatched before deletion (on entity and nested entities)
2. **Delete Operation** - Calls `Delete` model service
3. **POST_DELETE Event** - Dispatched after deletion
4. **Identity Map Cleanup** - Entity removed from tracking

#### Events Fired

- `EventManager::PRE_DELETE` - Before deletion
- `EventManager::POST_DELETE` - After deletion completes

**Note:** Unlike `persist()`, `remove()` does **not** cascade to related entities. Only the specified entity is deleted.

---

### clear() - Clear Entity Manager State

Clears all cached entities from the Identity Map and resets ChangeSet tracking.

#### Method Signature

```php
public function clear(): void
```

#### Example

```php
// After processing many entities in a long-running process
foreach ($largeDataSet as $item) {
    $video = $this->entityManager->find(Video::class, new ElasticId($item->id));
    // Process video...
}

// Clear to free memory and reset state
$this->entityManager->clear();

// Now all entities will be freshly loaded from ElasticSearch
$video = $this->entityManager->find(Video::class, new ElasticId('abc123'));
// This is a new instance, not from cache
```

#### How It Works

1. **Clears Identity Map** - All cached entity instances are removed
2. **Clears ChangeSet** - All entity tracking is reset

#### Use Cases

- **Long-running processes** - CLI commands, queue workers, batch imports where memory accumulates
- **Force fresh loads** - When you need to reload entities from database without cached state
- **Testing** - Reset state between test cases
- **Batch operations** - Clear between batches to prevent memory issues

#### Important Notes

- Does **not** affect persisted data in ElasticSearch
- Does **not** dispatch any events
- After clear, the same entity ID will return a **new object instance**
- Any in-memory changes to entities that weren't persisted will be lost

```php
// Example: Same ID, different instances after clear
$video1 = $this->entityManager->find(Video::class, $id);
$video2 = $this->entityManager->find(Video::class, $id);
$video1 === $video2; // true (same instance from Identity Map)

$this->entityManager->clear();

$video3 = $this->entityManager->find(Video::class, $id);
$video1 === $video3; // false (new instance after clear)
```

---

## Identity Map Integration

EntityManager automatically manages the Identity Map to ensure:
- Each entity is instantiated only once per request
- Changes are tracked
- Circular references don't cause infinite loops

### How It Works

```php
// First call - loads from ElasticSearch
$video1 = $this->entityManager->find(\App\Entity\Video::class, $id);

// Second call - returns same instance from Identity Map (no ES query)
$video2 = $this->entityManager->find(\App\Entity\Video::class, $id);

var_dump($video1 === $video2); // true (same object)
```

### Benefits

✅ **Performance** - Avoids redundant queries
✅ **Consistency** - Same entity = same object
✅ **Change Tracking** - Detects modifications
✅ **Circular Reference Prevention** - Handles complex entity graphs

### When Identity Map is Used

- `find()` - Checks map before querying
- `findBy()`, `findOneBy()` - Each hit checked against map
- `persist()` - Marks entity state in map
- Factory - All created entities added to map

---

## ChangeSet Tracking

EntityManager uses ChangeSet to determine if entities are new or existing, which controls which events fire.

### POST_CREATE vs POST_UPDATE

```php
// New entity
$video = new Video(new EmptyElasticId(), 'Title');
$this->entityManager->persist($video);
// Events: PRE_PERSIST → POST_PERSIST → POST_CREATE

// Existing entity
$video = $this->entityManager->find(Video::class, $id);
$this->entityManager->persist($video);
// Events: PRE_PERSIST → POST_PERSIST → POST_UPDATE
```

### How ChangeSet Works

1. **Factory marks entities as existing** - When loaded from ES
2. **New entities not marked** - Created in code with EmptyElasticId
3. **Persist checks ChangeSet** - Determines which event to fire

This allows listeners to distinguish between creation and updates:

```php
class NotifyOnCreateListener implements ListenerInterface
{
    public function getEvent(): string
    {
        return EventManager::POST_CREATE; // Only fires for NEW entities
    }

    public function handle(object|null $entity, object|null $parent): void
    {
        // Send "new video created" notification
    }
}
```

---

## Single Table Inheritance (STI) Support

EntityManager automatically handles STI entities when configured.

### Basic STI Setup

```php
// Parent class
abstract class Media extends AbstractElasticEntity
    implements STIElasticEntityInterface
{
    // Common properties
}

// Child classes
class Video extends Media { }
class Audio extends Media { }
```

### Index Config

```php
class MediaConfig implements IndexConfigInterface
{
    public function hasSti(): bool
    {
        return true; // Enable STI
    }

    public function provide(): Settings
    {
        // Mapping must include entityClass field
    }
}
```

### Usage

```php
// Save different subclasses to same index
$video = new Video(new EmptyElasticId(), 'Video Title');
$audio = new Audio(new EmptyElasticId(), 'Audio Title');

$this->entityManager->persist($video); // Saved with entityClass: "Video"
$this->entityManager->persist($audio); // Saved with entityClass: "Audio"

// Find returns correct subclass
$media = $this->entityManager->find(Media::class, $videoId);
// Returns Video instance, not Media
```

### How It Works

1. **Persist** - `Insert` adds `entityClass` field to document
2. **Find** - `EntityFactory` reads `entityClass` and instantiates correct subclass
3. **Identity Map** - Tracks by actual class, not parent class

---

## Event System Integration

All EntityManager operations dispatch lifecycle events.

### Available Events

- `EventManager::PRE_PERSIST` - Before save
- `EventManager::POST_PERSIST` - After save
- `EventManager::POST_CREATE` - After save (new entities only)
- `EventManager::POST_UPDATE` - After save (existing entities only)
- `EventManager::PRE_DELETE` - Before delete
- `EventManager::POST_DELETE` - After delete

### Event Propagation

Events fire recursively through entity tree:

```php
$video = new Video(
    new EmptyElasticId(),
    'Title',
    $technical, // EntityInterface - receives events
    $seasons,   // EntityCollectionInterface - each item receives events
    $cast       // ElasticEntityCollectionInterface - each item receives events
);

$this->entityManager->persist($video);

// Events fired on:
// - $video (main entity)
// - $technical (nested entity)
// - Each season in $seasons
// - Each person in $cast
```

**See:** [Event System Guide](19_event_system.md) for detailed event documentation.

---

## Common Patterns

### Pattern 1: Find or Create

```php
public function findOrCreate(string $slug): Video
{
    $query = new ElasticQuery();
    $query->query()->must()->add(new Term('slug', $slug));

    try {
        return $this->entityManager->findOneBy($query, Video::class);

    } catch (DocumentNotFound $e) {
        $video = new Video(
            new EmptyElasticId(),
            $slug,
            // ... properties
        );

        $this->entityManager->persist($video);

        return $video;
    }
}
```

---

### Pattern 2: Batch Load with Identity Map

```php
// Load videos by multiple IDs efficiently
public function loadVideos(array $ids): array
{
    $videos = [];

    foreach ($ids as $id) {
        $videos[] = $this->entityManager->find(
            Video::class,
            new ElasticId($id)
        );
    }

    return $videos;
}

// Duplicate IDs will return same object (Identity Map)
// ['id1', 'id2', 'id1'] → Two objects, third is reference
```

---

### Pattern 3: Conditional Update

```php
$video = $this->entityManager->find(Video::class, $id);

if ($video->status() === 'draft') {
    $video->publish();
    $this->entityManager->persist($video);
    // POST_UPDATE event fired
}
```

---

### Pattern 4: Related Entity Persistence

```php
$person = new Person(new EmptyElasticId(), 'Actor Name');

$video = $this->entityManager->find(Video::class, $videoId);
$video->cast->add($person); // Add to ElasticEntityCollection

$this->entityManager->persist($video);

// Both persisted:
// - Video updated in videos index
// - Person created in people index

// Later:
$loadedVideo = $this->entityManager->find(Video::class, $videoId);
foreach ($loadedVideo->cast as $actor) {
    // Lazy-loaded from people index
    echo $actor->name();
}
```

---

### Pattern 5: Pagination

```php
public function getPaginatedVideos(int $page, int $perPage): array
{
    $query = new ElasticQuery();
    $query->options()->setFrom(($page - 1) * $perPage);
    $query->options()->setSize($perPage);

    $videos = $this->entityManager->findBy($query, Video::class);

    return [
        'items' => $videos,
        'page' => $page,
        'perPage' => $perPage,
        'total' => count($videos), // Note: This is count of returned, not total
    ];
}
```

---

## Error Handling

### Common Exceptions

```php
try {
    $video = $this->entityManager->find(Video::class, $id);

} catch (\Spameri\Elastic\Exception\DocumentNotFound $e) {
    // Entity doesn't exist
    return $this->error('Video not found', 404);

} catch (\Spameri\Elastic\Exception\ElasticSearch $e) {
    // ElasticSearch error (connection, query syntax, etc.)
    \Tracy\Debugger::log($e);
    return $this->error('Search service unavailable', 503);

} catch (\Spameri\Elastic\Exception\SettingsNotLocated $e) {
    // Index config not found for entity class
    // This is a configuration error, not runtime error
    throw $e; // Should fail loudly in dev
}
```

### Best Practices

1. **Catch DocumentNotFound** - Expected scenario, handle gracefully
2. **Log ElasticSearch errors** - Unexpected, log for debugging
3. **Let SettingsNotLocated bubble** - Configuration issue, fix before production

---

## Performance Considerations

### When to Use find() vs findOneBy()

```php
// Fast - direct ID lookup
$video = $this->entityManager->find(Video::class, $id);

// Slower - executes search query
$query = new ElasticQuery();
$query->query()->must()->add(new Term('_id', $id));
$video = $this->entityManager->findOneBy($query, Video::class);
```

Always prefer `find()` when you have the document ID.

---

### Identity Map Efficiency

```php
// Efficient - one query, rest from cache
$ids = ['id1', 'id2', 'id3', 'id1', 'id2'];
foreach ($ids as $id) {
    $video = $this->entityManager->find(Video::class, new ElasticId($id));
    // 'id1', 'id2', 'id3' query ES
    // Second 'id1', 'id2' return from cache
}
```

---

### Avoid findAll() on Large Indices

```php
// Bad - loads entire index into memory
$allVideos = $this->entityManager->findAll(Video::class);

// Good - paginate with findBy()
$query = new ElasticQuery();
$query->options()->setFrom(0);
$query->options()->setSize(100);
$videos = $this->entityManager->findBy($query, Video::class);
```

---

## Debugging with Tracy

When `debug: true` in config, all EntityManager queries appear in Tracy panel.

**See:** [Tracy Debugging Guide](16_debugging.md) for details.

---

## Comparison: EntityManager vs Model Services

| Feature | EntityManager | Model Services |
|---------|---------------|----------------|
| **CRUD Operations** | ✅ Yes | ✅ Yes |
| **Type Safety** | ✅ Class-based | ❌ String indices |
| **Identity Map** | ✅ Automatic | ❌ Manual |
| **Events** | ✅ Automatic | ❌ Manual |
| **STI Support** | ✅ Automatic | ⚠️ Manual |
| **Aggregations** | ❌ No | ✅ Yes |
| **Bulk Operations** | ❌ No | ✅ Yes |
| **Index Management** | ❌ No | ✅ Yes |

### When to Use Each

**EntityManager** - Default choice for all CRUD operations
**Model Layer** - Aggregations, bulk operations, index management, scroll API

---

## Best Practices

### 1. Always Type-Hint Entity Class

```php
// Good - type-safe
$video = $this->entityManager->find(Video::class, $id);

// Bad - relies on strings
$entityClass = 'App\Entity\Video';
$video = $this->entityManager->find($entityClass, $id);
```

---

### 2. Use find() Over findOneBy() When Possible

```php
// Good - fast
$video = $this->entityManager->find(Video::class, $id);

// Avoid - slower
$query = new ElasticQuery();
$query->query()->must()->add(new Term('_id', $id->value()));
$video = $this->entityManager->findOneBy($query, Video::class);
```

---

### 3. Handle DocumentNotFound

```php
// Good
try {
    $video = $this->entityManager->find(Video::class, $id);
} catch (DocumentNotFound $e) {
    // Handle gracefully
}

// Bad - uncaught exception propagates
$video = $this->entityManager->find(Video::class, $id);
```

---

### 4. Trust the Identity Map

```php
// Good - trust that same ID returns same object
$video1 = $this->entityManager->find(Video::class, $id);
$video2 = $this->entityManager->find(Video::class, $id);
// No need to compare - they're identical objects

// Don't - unnecessary checks
if ($video1->id()->value() === $video2->id()->value()) {
    // Always true, they're the same object
}
```

---

### 5. Persist Only When Changed

```php
$video = $this->entityManager->find(Video::class, $id);

// Only persist if modified
if ($shouldUpdate) {
    $video->updateTitle($newTitle);
    $this->entityManager->persist($video);
}

// Don't - unnecessary persist
$video = $this->entityManager->find(Video::class, $id);
$this->entityManager->persist($video); // No changes, but still persists
```

---

## See Also

- [Entity Class](03_entity_class.md) - Entity structure and interfaces
- [Identity Map & ChangeSet](17_identity_map.md) - How tracking works
- [Event System](19_event_system.md) - Lifecycle events
- [Model Services](14_model_services.md) - Low-level operations
- [Basic Get](08_basic_get.md) - Simple retrieval examples
- [STI Guide](21_sti_guide.md) - Single Table Inheritance
