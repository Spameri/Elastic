# Identity Map

## Overview

The Identity Map is a design pattern that ensures each entity is loaded only once per request. When the same entity is requested multiple times, the Identity Map returns the same object instance, preventing duplicate queries and ensuring reference integrity.

## How It Works

```
┌─────────────────────────────────────────────────────────────────┐
│                         Request Lifecycle                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│   find(Video, "abc")  ──┬──► Identity Map Check                │
│                         │                                       │
│                         ├──► Cache HIT  → Return cached entity  │
│                         │                                       │
│                         └──► Cache MISS → Query ElasticSearch   │
│                                    │                            │
│                                    ▼                            │
│                              Create entity                      │
│                                    │                            │
│                                    ▼                            │
│                              Add to Identity Map                │
│                                    │                            │
│                                    ▼                            │
│                              Return entity                      │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Key Benefits

### 1. Reference Integrity

The same entity instance is returned regardless of how it's accessed:

```php
// First load via direct find
$video1 = $entityManager->find(Video::class, new ElasticId('abc123'));

// Second load via collection
$playlist = $entityManager->find(Playlist::class, new ElasticId('playlist1'));
$video2 = $playlist->videos()->entity(new ElasticId('abc123'));

// Same object instance!
$video1 === $video2; // true
```

### 2. Performance Optimization

Eliminates redundant queries:

```php
// Only ONE query to ElasticSearch
for ($i = 0; $i < 100; $i++) {
    $video = $entityManager->find(Video::class, new ElasticId('abc123'));
}
```

### 3. Consistency

Modifications are visible throughout the application:

```php
$video1 = $entityManager->find(Video::class, new ElasticId('abc123'));
$video1->incrementViews();

// Same object - sees the modification
$video2 = $entityManager->find(Video::class, new ElasticId('abc123'));
echo $video2->views(); // Reflects the incremented value
```

## IdentityMap Class

### Storage Structure

```php
class IdentityMap
{
    /**
     * Main entity storage: [className][id] => entity
     * @var array<class-string, array<string, AbstractElasticEntity>>
     */
    public array $identityMap = [];

    /**
     * Tracks persisted state for change detection
     * @var array<class-string, array<string, string>>
     */
    public array $persisted = [];

    /**
     * Tracks entities currently being created (circular reference prevention)
     * @var array<class-string, array<string, bool>>
     */
    public array $creatingEntityList = [];

    /**
     * Tracks uninitialized references (lazy loading)
     * @var array<class-string, array<string, array<string, array<string, class-string>>>>
     */
    public array $uninitializedEntityList = [];
}
```

### Core Methods

#### add()

Adds an entity to the Identity Map:

```php
public function add(AbstractElasticEntity $entity): void
```

**Behavior:**
- Skips entities with `EmptyElasticId` (new, unpersisted entities)
- Registers entity under its exact class
- Also registers under parent class (for STI support)

```php
// Internal storage after adding a Video entity:
$identityMap = [
    Video::class => [
        'abc123' => $videoInstance,
    ],
    Content::class => [  // Parent class (if Video extends Content)
        'abc123' => $videoInstance,
    ],
];
```

#### get()

Retrieves an entity from the Identity Map:

```php
public function get(string $class, string|int $id): AbstractElasticEntity|null
```

```php
$video = $identityMap->get(Video::class, 'abc123');
```

#### markInserted()

Marks an entity as persisted and stores its state for change detection:

```php
public function markInserted(AbstractElasticEntity $entity): void
```

#### isChanged()

Checks if an entity has changed since last persistence:

```php
public function isChanged(AbstractElasticEntity $entity): bool
```

The comparison uses MD5 hash of serialized entity variables.

#### clear()

Removes all entities from the Identity Map and resets all internal state:

```php
public function clear(): void
```

**Behavior:**
- Empties the `identityMap` array (all cached entities)
- Empties the `persisted` array (change tracking state)
- Empties the `creatingEntityList` array (circular reference tracking)
- Empties the `uninitializedEntityList` array (lazy loading references)

```php
// Clear all cached entities
$identityMap->clear();

// After clear, all entity lookups return null
$video = $identityMap->get(Video::class, 'abc123'); // null
```

**Use Cases:**
- Long-running processes (CLI commands, workers) where memory needs to be freed
- Batch operations where you want to force fresh entity loads
- Testing scenarios where you need to reset state between tests

## ChangeSet Integration

The `ChangeSet` class works alongside Identity Map to track entity lifecycle:

```php
class ChangeSet
{
    /**
     * Tracks entities loaded from database
     * @var array<string, array<string, bool>>
     */
    public array $created = [];

    public function markExisting(object $entity): void
    {
        $this->created[$entity::class][\spl_object_hash($entity)] = true;
    }

    public function isExisting(object $entity): bool
    {
        return isset($this->created[$entity::class][\spl_object_hash($entity)]);
    }

    public function clear(): void
    {
        $this->created = [];
    }
}
```

### Core Methods

#### markExisting()

Marks an entity as loaded from the database:

```php
public function markExisting(object $entity): void
```

#### isExisting()

Checks if an entity was loaded from the database:

```php
public function isExisting(object $entity): bool
```

#### clear()

Removes all tracked entities from the ChangeSet:

```php
public function clear(): void
```

**Behavior:**
- Empties the `created` array (all object tracking is lost)
- After clear, all entities will be treated as "new" (not existing)
- Typically called via `EntityManager::clear()`

```php
// After loading and clearing
$video = $entityManager->find(Video::class, $id);
$changeSet->isExisting($video); // true

$changeSet->clear();
$changeSet->isExisting($video); // false (treated as new)
```

### Purpose

- Determines whether to fire `POST_CREATE` or `POST_UPDATE` events
- Uses `spl_object_hash()` for instance tracking
- Works with any object (entities, nested entities, value objects)

### Event Flow

```php
// New entity
$video = new Video(new EmptyElasticId(), 'Title', 2024);
$entityManager->persist($video);
// ChangeSet::isExisting() returns false → POST_CREATE event

// Existing entity
$video = $entityManager->find(Video::class, new ElasticId('abc123'));
// EntityFactory calls ChangeSet::markExisting()
$entityManager->persist($video);
// ChangeSet::isExisting() returns true → POST_UPDATE event
```

## Usage Patterns

### Direct Access (Rarely Needed)

```php
class MyService
{
    public function __construct(
        private readonly \Spameri\Elastic\Model\IdentityMap $identityMap,
    ) {}

    public function isLoaded(string $id): bool
    {
        return $this->identityMap->get(Video::class, $id) !== null;
    }
}
```

### Preloading for Performance

Load entities you know you'll need:

```php
// Load all videos in one query
$videos = $entityManager->findAll(Video::class);

// Later access is instant (from Identity Map)
foreach ($videoIds as $id) {
    $video = $entityManager->find(Video::class, new ElasticId($id));
    // No query - served from Identity Map
}
```

### Avoiding N+1 Queries

```php
// Bad: N+1 queries
$playlists = $entityManager->findAll(Playlist::class);
foreach ($playlists as $playlist) {
    foreach ($playlist->videoIds() as $videoId) {
        $video = $entityManager->find(Video::class, new ElasticId($videoId));
        // Query per video!
    }
}

// Good: Preload then access
$playlists = $entityManager->findAll(Playlist::class);

// Collect all video IDs
$videoIds = [];
foreach ($playlists as $playlist) {
    $videoIds = \array_merge($videoIds, $playlist->videoIds());
}

// Single batch query
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->query()->addMust(
    new \Spameri\ElasticQuery\Query\Terms('_id', \array_unique($videoIds))
);
$videos = $entityManager->findBy(Video::class, $elasticQuery);

// Now all videos are in Identity Map - no additional queries
foreach ($playlists as $playlist) {
    foreach ($playlist->videoIds() as $videoId) {
        $video = $entityManager->find(Video::class, new ElasticId($videoId));
        // Served from Identity Map
    }
}
```

## Change Detection

The Identity Map tracks whether entities have changed since loading:

```php
// Load entity
$video = $entityManager->find(Video::class, new ElasticId('abc123'));

// Check if changed
$identityMap->isChanged($video); // false

// Modify entity
$video->setTitle('New Title');

// Now it's changed
$identityMap->isChanged($video); // true

// After persist, it's no longer changed
$entityManager->persist($video);
$identityMap->isChanged($video); // false
```

### How Change Detection Works

1. After loading/persisting, entity state is serialized and hashed
2. On `isChanged()`, current state is hashed and compared
3. Only scalar properties and IDs of related entities are compared
4. Collections are compared by their keys

## Circular Reference Handling

The Identity Map prevents infinite loops when loading entities with circular references:

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        #[ElasticCollection(Person::class)]
        public readonly PersonCollection $actors,
    ) {
        parent::__construct($id);
    }
}

class Person extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        #[ElasticCollection(Video::class)]
        public readonly VideoCollection $videos,
    ) {
        parent::__construct($id);
    }
}
```

The `creatingEntityList` tracks entities currently being constructed:

```php
// When creating Video 'v1':
$creatingEntityList[Video::class]['v1'] = true;

// If Person 'p1' references Video 'v1' during hydration:
// The factory checks creatingEntityList and skips or uses placeholder
```

## STI Support

The Identity Map handles Single Table Inheritance:

```php
// Video extends Content
$video = new Video(new ElasticId('abc123'), ...);
$identityMap->add($video);

// Can be retrieved via either class
$entity1 = $identityMap->get(Video::class, 'abc123');   // Returns Video
$entity2 = $identityMap->get(Content::class, 'abc123'); // Returns same Video
```

## Request Scope

The Identity Map is scoped to a single request:
- Fresh for each request
- All entities cleared when request ends
- No cross-request caching

This prevents stale data issues in long-running processes.

## Troubleshooting

### Entity Not Found Despite Existing

**Problem:** `find()` returns null even though document exists.

**Possible Causes:**
- Wrong entity class used in lookup
- ID mismatch (string vs int)
- Entity was removed but not from database

### Stale Data

**Problem:** Entity shows old values.

**Solution:** Identity Map returns cached instance. If data changed externally:
```php
// Force fresh load (not recommended in normal use)
// Consider if your design requires this
```

### Memory Issues

**Problem:** Too many entities in memory.

**Solution:**
- Use pagination for large result sets
- Process in batches
- Consider if you need all entities in memory

## Best Practices

1. **Trust the Cache** - Don't work around the Identity Map
2. **Use Preloading** - Load related entities in batches
3. **Avoid Manual Manipulation** - Let the EntityManager manage the map
4. **Design for Request Scope** - Don't assume cross-request persistence

## Next Steps

- [EntityManager Guide](17_entity_manager.md) - How Identity Map integrates
- [Event System](19_event_system.md) - ChangeSet and events
- [Model Services](14_model_services.md) - Low-level access
