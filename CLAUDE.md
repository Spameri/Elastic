# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **Spameri/Elastic** - an ElasticSearch implementation for the Nette Framework. The library provides a typed object-oriented interface to ElasticSearch, where queries, documents, and responses are all represented as PHP objects rather than raw arrays. It follows an ORM-like pattern with an EntityManager for persistence operations.

Key features:
- Typed entities extending `AbstractElasticEntity`
- EntityManager with persist/remove/find operations similar to Doctrine ORM
- Event system for lifecycle hooks (pre/post persist, create, update, delete)
- Single Table Inheritance (STI) support via `STIElasticEntityInterface`
- Type-safe query building via `spameri/elastic-query` package
- Index management commands via Symfony Console
- Import system with locking and progress tracking
- Identity Map pattern for entity caching
- Tracy debug bar integration

## Development Commands

### Testing
```bash
# Run all tests
make tests

# Run tests locally (single thread)
make tests-local

# Run single test file
vendor/bin/tester tests/SpameriTests/path/to/test.phpt
```

### Code Quality
```bash
# Run PHPStan static analysis (level 6)
make phpstan

# Run PHPStan with lowest dependencies
make phpstan-lowest

# Check coding standards
make cs

# Fix coding standards automatically
make csf

# Generate code coverage
make coverage
```

### Dependencies
```bash
# Update composer dependencies (stable)
make composer

# Update to lowest stable versions
make composer-lowest
```

### ElasticSearch Index Management
```bash
# Create an index (requires entity mapping class)
php bin/console spameri:elastic:create-index <index-name>

# Delete an index
php bin/console spameri:elastic:delete-index <index-name>

# Initialize all indexes from configuration
php bin/console spameri:elastic:initialize-index

# Dump index data to file
php bin/console spameri:elastic:dump-index <index-name>

# Load dumped data back (with optional step size for bulk operations)
php bin/console spameri:elastic:load-dump <file-path> [--step=500]

# Add/remove aliases
php bin/console spameri:elastic:add-alias <index-name> <alias>
php bin/console spameri:elastic:remove-alias <index-name> <alias>
```

## Architecture

### Core Components

**EntityManager** (`src/EntityManager.php`)
- Central entry point for all entity operations
- Methods: `find()`, `findOneBy()`, `findBy()`, `findAll()`, `persist()`, `remove()`
- Uses Identity Map pattern to cache entities and prevent duplicates
- Dispatches lifecycle events through EventManager
- All find methods return `ElasticEntityCollection` (except `findOneBy`)

**Entity Layer** (`src/Entity/`)
- All entities extend `AbstractElasticEntity` which implements `ElasticEntityInterface`
- Required property: `ElasticIdInterface $id` - managed by ElasticSearch
- Required methods: `id()`, `entityVariables()`
- Entity properties should use value objects implementing `ValueInterface` for validation
- STI support: entities can implement `STIElasticEntityInterface` for polymorphic storage

**Model Layer** (`src/Model/`)
- Service classes for specific operations: `Insert`, `GetAllBy`, `Delete`, `Search`, `Aggregate`, `Scroll`
- `EntitySettingsLocator` - maps entity classes to their index configurations
- `IdentityMap` - tracks loaded entities to prevent duplicates and enable references
- `ChangeSet` - tracks whether entity is new or existing for event dispatching
- `VersionProvider` - manages ElasticSearch version compatibility

**Factory Pattern** (`src/Factory/`)
- `EntityFactory` - creates entity instances from ElasticSearch Hit objects
- Uses reflection to construct entities with proper property types
- Integrates with Identity Map to reuse existing instances
- Handles nested objects and collections recursively

**Event System** (`src/EventManager.php`, `src/EventManager/`)
- Events: `PRE_PERSIST`, `POST_PERSIST`, `POST_CREATE`, `POST_UPDATE`, `PRE_DELETE`, `POST_DELETE`
- Listeners implement `ListenerInterface` with `getEvent()`, `getEntityClass()`, `handle()` methods
- Auto-discovered from DI container via `initListeners()`
- Events dispatch to both exact entity class matches AND parent classes
- Recursive event propagation through entity trees via `DispatchEvents`
- `ChangeSet` integration determines POST_CREATE vs POST_UPDATE
- Nested entity event propagation (fires for EntityInterface properties)
- Collection item event propagation (fires for items in EntityCollectionInterface)

**Import System** (`src/Import/`)
- `Run` and `SimpleRun` - orchestrate data imports with progress tracking
- `LockInterface` implementations - prevent concurrent imports (`FileLock`, `NullLock`)
- `DataProviderInterface` - source of import data
- `PrepareImportDataInterface` - transforms data before import
- `AfterImportInterface` - post-import hooks
- Exception hierarchy for error handling: `Fatal`, `Error`, `Omit`, `AlreadyLocked`

**Mapping System** (`src/Mapping/`)
- Attributes for entity mapping: `@Entity`, `@Collection`, `@ElasticCollection`, `@STIEntity`, `@STIElasticEntity`, `@Ignored`
- Used by reflection to understand entity structure during persistence/hydration

**Settings & Configuration** (`src/Settings/`, `src/DI/`)
- `SpameriElasticSearchExtension` - Nette DI extension for configuration
- Config options: `host`, `port`, `debug`, `version`, `synonymPath`, `entities`
- `IndexConfigInterface` - defines index mappings via `provide()` returning `\Spameri\ElasticQuery\Mapping\Settings`
- Index configurations use `spameri/elastic-query` objects: `Field`, `SubFields`, `FieldObject`, `FieldCollection`

**Query Building**
- Uses external `spameri/elastic-query` package for type-safe queries
- Main entry point: `\Spameri\ElasticQuery\ElasticQuery`
- Query types: `Term`, `Match`, `Range`, `Bool`, aggregations, etc.
- Passed to EntityManager's `findBy()` or Model layer services

### Important Patterns

**Entity Construction**
- Entities must accept all properties via constructor (used by EntityFactory)
- ID property is always first parameter and typed as `ElasticIdInterface`
- Value objects implementing `ValueInterface` provide type safety and validation
- Use `EmptyElasticId` for new entities, `ElasticId` for existing ones

**Persistence Flow**
1. Call `EntityManager->persist($entity)`
2. EventManager dispatches `PRE_PERSIST` event
3. `Insert` service converts entity to array via reflection
4. Data sent to ElasticSearch
5. EventManager dispatches `POST_PERSIST`, plus `POST_CREATE` or `POST_UPDATE` based on ChangeSet
6. Entity added to Identity Map

**Retrieval Flow**
1. Call `EntityManager->findBy($query, $class)`
2. `GetAllBy` executes query against index (determined by EntitySettingsLocator)
3. Each Hit processed by `EntityFactory->create()`
4. Factory checks Identity Map first (returns cached if exists)
5. Factory uses reflection to resolve constructor parameters
6. Nested entities/collections created recursively
7. New entity added to Identity Map
8. Returns `ElasticEntityCollection` containing results

**Single Table Inheritance (STI)**
- Parent entity implements `STIElasticEntityInterface`
- Child entities extend parent
- Index stores `entityClass` field to identify concrete type
- Factory automatically instantiates correct child class on retrieval
- Enabled via `hasSti()` in index config

## Configuration Example

```neon
extensions:
    spameriElasticSearch: \Spameri\Elastic\DI\SpameriElasticSearchExtension

spameriElasticSearch:
    host: 127.0.0.1
    port: 9200
    debug: true  # Enables Tracy debug bar panel
    version: 8  # ElasticSearch version

services:
    - App\Model\Settings\MyEntityMapping  # IndexConfigInterface implementation
```

## Code Style

- PHP 8.2+ with strict types (`declare(strict_types = 1)`)
- Constructor property promotion with readonly where applicable
- Fully qualified class names in code (enforced by coding standard)
- Slevomat Coding Standard rules (see `ruleset.xml`)
- PHPStan level 6 analysis
- Properties typed strictly; use union types sparingly
- Nette Tester for unit tests (`.phpt` files)

## Important Conventions

**Never use field named `id` in entity mapping** - it conflicts with ElasticSearch's internal `_id`. Use `databaseId`, `externalId`, etc.

**Entity property order matters** - constructor parameter order must match the order reflection discovers them in.

**Index naming** - use consistent naming between entity config, mapping class, and actual index names.

**Test structure** - tests in `tests/SpameriTests/` mirror `src/` structure; bootstrap purges test indexes before each run.

**ElasticQuery integration** - always use `\Spameri\ElasticQuery\ElasticQuery` objects for queries, not raw arrays, to maintain type safety.

**Event listeners** - must implement `ListenerInterface` and be registered in DI container to be auto-discovered by EventManager.

**Value objects** - prefer value objects implementing `ValueInterface` for entity properties to encapsulate validation logic.

## Event System Deep Dive

### Event Lifecycle

**Persist Flow (EntityManager::persist):**
1. `PRE_PERSIST` - Before any changes
   - Dispatched on main entity
   - Dispatched recursively on all nested entities/collections via `DispatchEvents`
2. Entity saved to ElasticSearch via `Insert`
3. `POST_PERSIST` - After save
   - Dispatched on main entity
   - Dispatched recursively on all nested entities/collections
4. `POST_CREATE` or `POST_UPDATE` - Based on ChangeSet
   - If `ChangeSet::isExisting($entity)` is false → `POST_CREATE`
   - If `ChangeSet::isExisting($entity)` is true → `POST_UPDATE`
   - Dispatched on main entity first
   - Dispatched recursively (POST_UPDATE only) on nested entities
   - POST_CREATE uses ChangeSet filtering during recursion
5. Final recursive dispatch of POST_CREATE and POST_PERSIST

**Delete Flow (EntityManager::remove):**
1. `PRE_DELETE` - Before deletion
   - Dispatched on main entity
   - Dispatched recursively on all nested entities/collections
2. Entity deleted from ElasticSearch via `Delete`
3. `POST_DELETE` - After deletion
   - Dispatched on main entity
   - Dispatched recursively on all nested entities/collections

### ChangeSet System

**Purpose:** Tracks whether entities are new or existing to determine correct lifecycle events.

**Key Methods:**
- `markExisting($entity)` - Marks entity as loaded from database (called by EntityFactory)
- `isExisting($entity)` - Returns true if entity was previously marked

**How It Works:**
- Uses `spl_object_hash()` for entity identity tracking
- Stored as: `$created[$className][$objectHash] = true`
- Works with any object, not just `ElasticEntityInterface`

**Integration:**
```php
// EntityManager::persist()
if ($this->changeSet->isExisting($entity) === false) {
    // Fire POST_CREATE
} else {
    // Fire POST_UPDATE
}
```

### DispatchEvents - Recursive Event Propagation

**Purpose:** Walks entity tree and fires events on nested entities and collections.

**Entry Point:**
```php
$this->dispatchEvents->execute($entity, EventManager::PRE_PERSIST);
```

**Propagation Flow:**
1. Calls `iterateVariables()` with entity's properties
2. For each property:
   - If `EntityInterface` → dispatch event + recurse into its properties
   - If `EntityCollectionInterface` → iterate items, dispatch + recurse for each
   - Otherwise → skip (scalars, value objects, etc.)
3. Recursion continues through entire entity tree

**Special POST_CREATE Handling:**
- POST_CREATE events are conditionally dispatched based on `ChangeSet`
- Only fires if `ChangeSet::isExisting($property)` returns false
- Prevents firing CREATE events for existing nested entities loaded from database

**Example Tree:**
```
Video (ElasticEntity)
├── Technical (EntityInterface) ← Events fired here
│   ├── Resolution (scalar) ← No events
│   └── Codec (scalar) ← No events
├── Seasons (EntityCollection) ← Events fired on each item
│   ├── Season 1 (EntityInterface) ← Events fired here
│   │   └── Episodes (EntityCollection) ← Events fired on each
│   └── Season 2 (EntityInterface) ← Events fired here
└── Cast (ElasticEntityCollection) ← Events fired on each item
    ├── Person 1 (ElasticEntity) ← Events fired here
    └── Person 2 (ElasticEntity) ← Events fired here
```

All entities in this tree receive appropriate lifecycle events.

### Listener Auto-Discovery

**How It Works:**
1. On first event dispatch, `EventManager::initListeners()` is called
2. Searches DI container for all services implementing `ListenerInterface`
3. For each listener:
   - Calls `getEvent()` - which event to listen for
   - Calls `getEntityClass()` - which entity classes (returns array)
   - Registers listener for each class/event combination

**Inheritance Matching:**
Events dispatch to listeners registered for:
- The exact entity class
- ANY parent class or interface

Example:
```php
class VideoListener implements ListenerInterface {
    public function getEntityClass(): array {
        return [AbstractElasticEntity::class]; // Matches ALL entities
    }
}

class SpecificVideoListener implements ListenerInterface {
    public function getEntityClass(): array {
        return [Video::class]; // Only Video entities
    }
}
```

### Creating Event Listeners

**1. Implement ListenerInterface:**
```php
namespace App\EventListener;

class VideoPersistedListener implements \Spameri\Elastic\EventManager\ListenerInterface
{
    public function __construct(
        private \Psr\Log\LoggerInterface $logger
    ) {}

    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof \App\Entity\Video) {
            $this->logger->info('Video persisted', [
                'id' => $entity->id()->value(),
                'title' => $entity->title(),
            ]);
        }
    }

    public function getEntityClass(): array
    {
        return [\App\Entity\Video::class];
    }

    public function getEvent(): string
    {
        return \Spameri\Elastic\EventManager::POST_PERSIST;
    }
}
```

**2. Register in DI:**
```neon
services:
    - App\EventListener\VideoPersistedListener
```

**That's it!** No manual registration needed - auto-discovered on first event.

### Parent Entity Access

Listeners receive both the entity and its parent:

```php
public function handle(object|null $entity, object|null $parent): void
{
    // $entity = the entity the event fired for
    // $parent = the entity containing this one (for nested entities)

    if ($entity instanceof Season && $parent instanceof Video) {
        // Season was saved as part of Video
    }
}
```

### Common Patterns

**1. Invalidate Cache After Update:**
```php
class InvalidateCacheListener implements ListenerInterface
{
    public function __construct(private CacheInterface $cache) {}

    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->cache->delete('video.' . $entity->id()->value());
        }
    }

    public function getEntityClass(): array
    {
        return [Video::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_UPDATE;
    }
}
```

**2. Send Notification After Creation:**
```php
class NotifyOnCreateListener implements ListenerInterface
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->notifications->send(
                'New video created: ' . $entity->title()
            );
        }
    }

    public function getEntityClass(): array
    {
        return [Video::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_CREATE; // Only on creation, not updates
    }
}
```

**3. Update Related Data Before Persist:**
```php
class UpdateTimestampListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $entity->setModifiedAt(new \DateTime());
        }
    }

    public function getEntityClass(): array
    {
        return [Video::class];
    }

    public function getEvent(): string
    {
        return EventManager::PRE_PERSIST; // Before save
    }
}
```

**4. Listen to All Entities (Global Listener):**
```php
class GlobalAuditListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        // Log ALL entity persists
        if ($entity !== null) {
            $this->auditLog->record($entity::class, 'persisted');
        }
    }

    public function getEntityClass(): array
    {
        // Empty string or base class matches all
        return [AbstractElasticEntity::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_PERSIST;
    }
}
```

## Testing Notes

- Tests require running ElasticSearch instance (configured via `SpameriTests\Elastic\Config`)
- Bootstrap automatically deletes test indexes before test run
- Tests use `.phpt` format (Nette Tester)
- Temporary directory: `tests/tmp/` (auto-cleaned)
- Test data: `tests/SpameriTests/data.json` (1.2MB fixture file)
