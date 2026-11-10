# Event System Guide

## Overview

The Event System provides lifecycle hooks for ElasticSearch entity operations. It allows you to execute code automatically before and after persistence operations, enabling features like:

- Cache invalidation
- Audit logging
- Notifications
- Data validation
- Related entity updates
- Webhook triggers

**Location:** `src/EventManager.php`, `src/EventManager/`

**Key Features:**
- 6 lifecycle events (PRE/POST for persist, create, update, delete)
- Automatic listener discovery from DI container
- Recursive event propagation through entity trees
- Parent entity access in handlers
- Inheritance-based event matching

---

## Available Events

### Persistence Events

#### PRE_PERSIST
```php
EventManager::PRE_PERSIST = 'pre_persist'
```

**Fires:** Before entity is saved to ElasticSearch
**Use For:** Validation, data preparation, pre-save modifications
**Can Modify:** Entity can be modified before save

**Example:**
```php
class UpdateTimestampListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $entity->setModifiedAt(new \DateTime());
        }
    }

    public function getEvent(): string
    {
        return EventManager::PRE_PERSIST;
    }
}
```

---

#### POST_PERSIST
```php
EventManager::POST_PERSIST = 'post_persist'
```

**Fires:** After entity is saved to ElasticSearch
**Use For:** Post-save actions, logging, notifications
**Note:** Entity now has ID assigned if it was new

**Example:**
```php
class LogPersistListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->logger->info('Video persisted', [
                'id' => $entity->id()->value(),
                'title' => $entity->title(),
            ]);
        }
    }

    public function getEvent(): string
    {
        return EventManager::POST_PERSIST;
    }
}
```

---

#### POST_CREATE
```php
EventManager::POST_CREATE = 'post_create'
```

**Fires:** After new entity is created in ElasticSearch
**Use For:** Creation-specific actions, welcome emails, analytics
**Distinction:** Only fires for NEW entities (with EmptyElasticId before persist)

**Example:**
```php
class NotifyOnCreateListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->notificationService->send(
                'New video created: ' . $entity->title()
            );
        }
    }

    public function getEvent(): string
    {
        return EventManager::POST_CREATE; // Only on creation
    }
}
```

---

#### POST_UPDATE
```php
EventManager::POST_UPDATE = 'post_update'
```

**Fires:** After existing entity is updated in ElasticSearch
**Use For:** Update-specific actions, change notifications, cache invalidation
**Distinction:** Only fires for EXISTING entities (had ElasticId before persist)

**Example:**
```php
class InvalidateCacheListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->cache->delete('video.' . $entity->id()->value());
        }
    }

    public function getEvent(): string
    {
        return EventManager::POST_UPDATE; // Only on updates
    }
}
```

---

### Deletion Events

#### PRE_DELETE
```php
EventManager::PRE_DELETE = 'pre_delete'
```

**Fires:** Before entity is deleted from ElasticSearch
**Use For:** Pre-deletion checks, related entity cleanup, backups

**Example:**
```php
class BackupBeforeDeleteListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->backup->store($entity);
        }
    }

    public function getEvent(): string
    {
        return EventManager::PRE_DELETE;
    }
}
```

---

#### POST_DELETE
```php
EventManager::POST_DELETE = 'post_delete'
```

**Fires:** After entity is deleted from ElasticSearch
**Use For:** Cleanup, notifications, logging

**Example:**
```php
class CleanupAfterDeleteListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->fileStorage->deleteFiles($entity->id()->value());
        }
    }

    public function getEvent(): string
    {
        return EventManager::POST_DELETE;
    }
}
```

---

## Event Lifecycle

### Persist Flow

When you call `$entityManager->persist($entity)`, events fire in this order:

```
1. PRE_PERSIST
   ├─ Fired on main entity
   └─ Recursively fired on all nested entities/collections

2. Entity saved to ElasticSearch
   └─ Insert service executes

3. POST_PERSIST
   ├─ Fired on main entity
   └─ Recursively fired on all nested entities/collections

4. POST_CREATE or POST_UPDATE (determined by ChangeSet)
   ├─ If entity was new (EmptyElasticId) → POST_CREATE
   │  ├─ Fired on main entity
   │  └─ Conditionally fired on nested entities (if they're also new)
   │
   └─ If entity existed (had ElasticId) → POST_UPDATE
      ├─ Fired on main entity
      └─ Recursively fired on nested entities
```

**Example with nested entities:**

```php
$video = new Video(
    new EmptyElasticId(),
    'Title',
    $technical,  // EntityInterface
    $seasons     // EntityCollectionInterface
);

$entityManager->persist($video);

// Events fired:
// 1. PRE_PERSIST on $video
// 2. PRE_PERSIST on $technical
// 3. PRE_PERSIST on each season in $seasons
// 4. [Save to ES]
// 5. POST_PERSIST on $video
// 6. POST_PERSIST on $technical
// 7. POST_PERSIST on each season in $seasons
// 8. POST_CREATE on $video (was new)
// 9. POST_CREATE on $technical (if new)
// 10. POST_CREATE on each new season
```

---

### Delete Flow

When you call `$entityManager->remove($entity)`:

```
1. PRE_DELETE
   ├─ Fired on main entity
   └─ Recursively fired on all nested entities/collections

2. Entity deleted from ElasticSearch
   └─ Delete service executes

3. POST_DELETE
   ├─ Fired on main entity
   └─ Recursively fired on all nested entities/collections
```

---

## Creating Event Listeners

### Step 1: Implement ListenerInterface

```php
<?php

namespace App\EventListener;

use Spameri\Elastic\EventManager;
use Spameri\Elastic\EventManager\ListenerInterface;

class VideoPersistedListener implements ListenerInterface
{
    public function __construct(
        private \Psr\Log\LoggerInterface $logger
    ) {}

    public function handle(object|null $entity, object|null $parent): void
    {
        // Type check first
        if ($entity instanceof \App\Entity\Video) {
            $this->logger->info('Video persisted', [
                'id' => $entity->id()->value(),
                'title' => $entity->title(),
            ]);
        }
    }

    public function getEntityClass(): array
    {
        // Return array of entity classes to listen for
        return [\App\Entity\Video::class];
    }

    public function getEvent(): string
    {
        // Which event to listen for
        return EventManager::POST_PERSIST;
    }
}
```

---

### Step 2: Register in DI Container

```neon
services:
    - App\EventListener\VideoPersistedListener
```

**That's it!** No manual registration needed. Listeners are auto-discovered from the DI container.

---

## Listener Interface Methods

### handle()

```php
public function handle(object|null $entity, object|null $parent): void
```

**Parameters:**
- `$entity` - The entity the event fired for (the target)
- `$parent` - The parent entity containing this entity (null for top-level)

**Example with parent:**

```php
public function handle(object|null $entity, object|null $parent): void
{
    // Season nested in Video
    if ($entity instanceof Season && $parent instanceof Video) {
        $this->logger->info('Season added to video', [
            'season' => $entity->number(),
            'video' => $parent->title(),
        ]);
    }
}
```

---

### getEntityClass()

```php
public function getEntityClass(): array
```

**Returns:** Array of entity class names to listen for

**Examples:**

```php
// Listen for specific entity
public function getEntityClass(): array
{
    return [\App\Entity\Video::class];
}

// Listen for multiple entities
public function getEntityClass(): array
{
    return [
        \App\Entity\Video::class,
        \App\Entity\Audio::class,
    ];
}

// Listen for ALL entities (use base class)
public function getEntityClass(): array
{
    return [\Spameri\Elastic\Entity\AbstractElasticEntity::class];
}

// Listen for interface implementers
public function getEntityClass(): array
{
    return [\App\Entity\MediaInterface::class];
}
```

---

### getEvent()

```php
public function getEvent(): string
```

**Returns:** Event constant from `EventManager`

**Available events:**
- `EventManager::PRE_PERSIST`
- `EventManager::POST_PERSIST`
- `EventManager::POST_CREATE`
- `EventManager::POST_UPDATE`
- `EventManager::PRE_DELETE`
- `EventManager::POST_DELETE`

---

## Listener Auto-Discovery

### How It Works

1. **First Event Dispatch:** `EventManager::initListeners()` is called
2. **DI Scan:** Container scanned for services implementing `ListenerInterface`
3. **Registration:** For each listener:
   - Get event via `getEvent()`
   - Get entity classes via `getEntityClass()`
   - Register listener for each class/event combination
4. **Inheritance Matching:** Events also dispatch to parent class listeners

**Example:**

```php
// Listener registered for AbstractElasticEntity
class GlobalAuditListener implements ListenerInterface
{
    public function getEntityClass(): array
    {
        return [\Spameri\Elastic\Entity\AbstractElasticEntity::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_PERSIST;
    }
}

// Receives events for ALL entities
$entityManager->persist($video);    // ✅ Fires
$entityManager->persist($person);   // ✅ Fires
$entityManager->persist($product);  // ✅ Fires
```

---

## ChangeSet System

The ChangeSet tracks whether entities are new or existing to determine which events fire.

### How It Works

```php
// New entity - created in code
$video = new Video(new EmptyElasticId(), 'Title');
// ChangeSet: NOT marked as existing

$entityManager->persist($video);
// Events: PRE_PERSIST → POST_PERSIST → POST_CREATE

// ---

// Existing entity - loaded from ES
$video = $entityManager->find(Video::class, $id);
// ChangeSet: Marked as existing by EntityFactory

$entityManager->persist($video);
// Events: PRE_PERSIST → POST_PERSIST → POST_UPDATE
```

### Key Methods

**markExisting($entity)**
- Called by `EntityFactory` when loading entities
- Stores entity hash in ChangeSet
- Indicates entity came from database

**isExisting($entity)**
- Returns `true` if entity was marked
- Returns `false` for new entities
- Used by EventManager to choose POST_CREATE vs POST_UPDATE

### Entity Hash Tracking

Uses `spl_object_hash()` for identity:

```php
$hash = spl_object_hash($entity);
$this->created[$className][$hash] = true;
```

This allows tracking ANY object, not just ElasticSearch entities.

---

## Recursive Event Propagation

Events automatically propagate through the entity tree via the `DispatchEvents` class.

### How It Works

```php
// Video with nested structure
$video = new Video(
    new EmptyElasticId(),
    'Title',
    $technical,  // EntityInterface
    $seasons,    // EntityCollectionInterface (contains multiple Season entities)
    $cast        // ElasticEntityCollectionInterface (contains Person entities)
);

$entityManager->persist($video);
```

**Event Flow:**

```
Video (ElasticEntity)
├─ Technical (EntityInterface)
│  └─ Events: PRE_PERSIST, POST_PERSIST
├─ Seasons (EntityCollectionInterface)
│  ├─ Season 1 (EntityInterface)
│  │  └─ Events: PRE_PERSIST, POST_PERSIST, POST_CREATE
│  └─ Season 2 (EntityInterface)
│     └─ Events: PRE_PERSIST, POST_PERSIST, POST_CREATE
└─ Cast (ElasticEntityCollectionInterface)
   ├─ Person 1 (ElasticEntity)
   │  └─ Events: PRE_PERSIST, POST_PERSIST, POST_CREATE
   └─ Person 2 (ElasticEntity)
      └─ Events: PRE_PERSIST, POST_PERSIST, POST_CREATE
```

**All entities in this tree receive appropriate events.**

### Propagation Rules

1. **EntityInterface properties** → Events fired + recursive propagation
2. **EntityCollectionInterface** → Iterate items, fire + recurse for each
3. **ElasticEntityCollectionInterface** → Same as EntityCollectionInterface
4. **Scalar/Value properties** → No events (skipped)

### POST_CREATE Conditional Firing

POST_CREATE only fires for entities that are new (per ChangeSet):

```php
// Video with existing Technical but new Season
$video = $entityManager->find(Video::class, $id);  // Existing
$technical = $video->technical();                   // Existing (loaded with video)
$newSeason = new Season(...);                       // New
$video->seasons()->add($newSeason);

$entityManager->persist($video);

// POST_CREATE fires for:
// - $newSeason ✅ (new)
//
// POST_UPDATE fires for:
// - $video ✅ (existing)
// - $technical ✅ (existing)
```

---

## Common Patterns

### Pattern 1: Cache Invalidation

```php
class InvalidateCacheListener implements ListenerInterface
{
    public function __construct(
        private \Psr\Cache\CacheItemPoolInterface $cache
    ) {}

    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->cache->deleteItem('video.' . $entity->id()->value());
            $this->cache->deleteItem('videos.list'); // List cache
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

---

### Pattern 2: Send Notifications

```php
class NotifyOnCreateListener implements ListenerInterface
{
    public function __construct(
        private NotificationService $notifications
    ) {}

    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            $this->notifications->send(
                'new_video',
                [
                    'title' => $entity->title(),
                    'id' => $entity->id()->value(),
                ]
            );
        }
    }

    public function getEntityClass(): array
    {
        return [Video::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_CREATE; // Only new videos
    }
}
```

---

### Pattern 3: Pre-Persist Validation

```php
class ValidateVideoListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        if ($entity instanceof Video) {
            if (empty($entity->title())) {
                throw new \InvalidArgumentException('Video title is required');
            }

            if ($entity->year() < 1900 || $entity->year() > 2100) {
                throw new \InvalidArgumentException('Invalid year');
            }
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

---

### Pattern 4: Audit Logging

```php
class AuditLogListener implements ListenerInterface
{
    public function __construct(
        private AuditLogger $auditLog,
        private Security $security
    ) {}

    public function handle(object|null $entity, object|null $parent): void
    {
        $this->auditLog->log([
            'action' => 'entity_persisted',
            'entity_class' => get_class($entity),
            'entity_id' => $entity instanceof ElasticEntityInterface
                ? $entity->id()->value()
                : null,
            'user' => $this->security->getUser()?->getUsername(),
            'timestamp' => new \DateTime(),
        ]);
    }

    public function getEntityClass(): array
    {
        // Listen to ALL entities
        return [AbstractElasticEntity::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_PERSIST;
    }
}
```

---

### Pattern 5: Update Timestamps

```php
class TimestampListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        // Assumes entities have setModifiedAt() method
        if (method_exists($entity, 'setModifiedAt')) {
            $entity->setModifiedAt(new \DateTime());
        }
    }

    public function getEntityClass(): array
    {
        // All entities with timestamp support
        return [TimestampedInterface::class];
    }

    public function getEvent(): string
    {
        return EventManager::PRE_PERSIST; // Before save
    }
}
```

---

### Pattern 6: Related Entity Updates

```php
class UpdateVideoCountListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        // When Person is added to Video cast, update Person's video count
        if ($entity instanceof Person && $parent instanceof Video) {
            $entity->incrementVideoCount();
        }
    }

    public function getEntityClass(): array
    {
        return [Person::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_PERSIST;
    }
}
```

---

## Multiple Listeners for Same Event

You can have multiple listeners for the same event/entity combination:

```php
// Listener 1: Cache invalidation
class CacheInvalidationListener implements ListenerInterface
{
    public function getEntityClass(): array { return [Video::class]; }
    public function getEvent(): string { return EventManager::POST_UPDATE; }
}

// Listener 2: Search index update
class SearchIndexListener implements ListenerInterface
{
    public function getEntityClass(): array { return [Video::class]; }
    public function getEvent(): string { return EventManager::POST_UPDATE; }
}

// Both will fire when Video is updated
```

**Execution Order:** Listeners fire in the order they were discovered from DI (non-deterministic). Don't rely on specific order.

---

## Inheritance-Based Matching

Listeners registered for parent classes receive events for child classes:

```php
abstract class Media extends AbstractElasticEntity { }
class Video extends Media { }
class Audio extends Media { }

class MediaListener implements ListenerInterface
{
    public function getEntityClass(): array
    {
        return [Media::class]; // Parent class
    }

    public function getEvent(): string
    {
        return EventManager::POST_PERSIST;
    }
}

// Listener fires for:
$entityManager->persist($video); // ✅ Video extends Media
$entityManager->persist($audio); // ✅ Audio extends Media
```

---

## Performance Considerations

### Event Overhead

Each event dispatch:
1. Looks up registered listeners
2. Iterates through entity tree (for recursive events)
3. Calls each listener's `handle()` method

**Typical overhead:** ~1-5ms per persist operation (depends on listener count and complexity)

### Optimization Tips

1. **Keep listeners lightweight**
   ```php
   // Good - queue heavy work
   public function handle(object|null $entity, object|null $parent): void
   {
       $this->queue->push(new SendEmailJob($entity));
   }

   // Bad - heavy work in listener
   public function handle(object|null $entity, object|null $parent): void
   {
       $this->emailService->sendComplexEmail($entity); // Blocks persist
   }
   ```

2. **Use specific entity classes**
   ```php
   // Good - only Video events
   public function getEntityClass(): array
   {
       return [Video::class];
   }

   // Avoid if possible - ALL entities
   public function getEntityClass(): array
   {
       return [AbstractElasticEntity::class];
   }
   ```

3. **Choose right event**
   ```php
   // Good - only fires for new entities
   return EventManager::POST_CREATE;

   // Avoid - fires for ALL persists
   return EventManager::POST_PERSIST;
   ```

---

## Debugging Events

### Check Registered Listeners

```php
// In dev/debug mode
public function __construct(
    private EventManager $eventManager
) {}

public function debugAction(): void
{
    // Trigger listener initialization
    $this->eventManager->initListeners();

    // Inspect registered listeners (private, use reflection or Tracy panel)
}
```

### Log Events

```php
class DebugEventListener implements ListenerInterface
{
    public function handle(object|null $entity, object|null $parent): void
    {
        \Tracy\Debugger::barDump([
            'event' => $this->getEvent(),
            'entity' => get_class($entity),
            'parent' => $parent ? get_class($parent) : null,
        ], 'Event Fired');
    }

    public function getEntityClass(): array
    {
        return [AbstractElasticEntity::class];
    }

    public function getEvent(): string
    {
        return EventManager::POST_PERSIST; // Or any event
    }
}
```

---

## Best Practices

### 1. Type Check in Handlers

Always check entity type first:

```php
public function handle(object|null $entity, object|null $parent): void
{
    // Good - type check
    if ($entity instanceof Video) {
        // Handle video
    }

    // Bad - assumes type
    $entity->someVideoMethod(); // May crash if not Video
}
```

---

### 2. Return Early

```php
public function handle(object|null $entity, object|null $parent): void
{
    // Good - early return
    if (!$entity instanceof Video) {
        return;
    }

    // Handle video...

    // Bad - deep nesting
    if ($entity instanceof Video) {
        if ($entity->isPublished()) {
            if ($entity->hasImage()) {
                // Deep nesting is hard to read
            }
        }
    }
}
```

---

### 3. Use Specific Events

```php
// Good - specific intent
return EventManager::POST_CREATE; // Only new entities

// Avoid - too broad
return EventManager::POST_PERSIST; // All persists
```

---

### 4. Queue Heavy Work

```php
// Good - async processing
public function handle(object|null $entity, object|null $parent): void
{
    $this->queue->push(new ProcessVideoJob($entity->id()));
}

// Bad - blocks request
public function handle(object|null $entity, object|null $parent): void
{
    $this->transcoder->transcodeVideo($entity); // Takes 30 seconds
}
```

---

### 5. Handle Exceptions

```php
public function handle(object|null $entity, object|null $parent): void
{
    try {
        $this->externalApi->notify($entity);
    } catch (\Throwable $e) {
        // Log but don't crash persist
        \Tracy\Debugger::log($e);
    }
}
```

---

## Troubleshooting

### Listener Not Firing

**Possible causes:**

1. **Not registered in DI**
   ```neon
   services:
       - App\EventListener\MyListener  # ← Must be registered
   ```

2. **Wrong entity class**
   ```php
   // Check you're listening for the right class
   public function getEntityClass(): array
   {
       return [Video::class]; // Exact class or parent
   }
   ```

3. **Wrong event**
   ```php
   // Verify event name
   return EventManager::POST_PERSIST; // Use constants
   ```

4. **Listener not implementing interface**
   ```php
   // Must implement
   class MyListener implements \Spameri\Elastic\EventManager\ListenerInterface
   ```

---

### Event Fires Multiple Times

**Cause:** Listener registered for parent class receives events for all children

**Solution:** Be more specific in `getEntityClass()` or add type check in `handle()`

---

### Events Not Recursive

**Cause:** Only top-level entity receives events (nested entities don't)

**Reason:** This is expected for some event types. Check event propagation rules.

**Solution:** Ensure nested entities implement correct interfaces (`EntityInterface`, etc.)

---

## See Also

- [EntityManager Guide](17_entity_manager.md) - How persist/remove dispatch events
- [Entity Class](03_entity_class.md) - Entity structure and interfaces
- [Identity Map](17_identity_map.md) - ChangeSet and entity tracking
- [Model Services](14_model_services.md) - Low-level operations
