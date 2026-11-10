# Entity Factory

## Overview

The `EntityFactory` is responsible for creating entity instances from ElasticSearch hit results. The library provides a fully-featured, reflection-based factory that handles all entity hydration automatically.

> **Note:** Custom entity factories are deprecated and should not be used. The built-in `EntityFactory` handles all use cases including nested entities, collections, STI, circular references, and Identity Map integration.

## How It Works

When you retrieve entities via `EntityManager`, the built-in `EntityFactory` automatically:

1. **Checks Identity Map** - Returns cached instance if entity was already loaded
2. **Reads Constructor Parameters** - Uses reflection to determine required properties
3. **Maps Hit Data** - Converts ElasticSearch document fields to constructor arguments
4. **Handles Nested Entities** - Recursively creates nested `EntityInterface` objects
5. **Handles Collections** - Populates `EntityCollectionInterface` and `ElasticEntityCollection` properties
6. **Supports STI** - Instantiates correct child class based on stored `entityClass` field
7. **Tracks Changes** - Marks entities in `ChangeSet` for proper lifecycle events
8. **Manages Circular References** - Handles bidirectional relationships between entities

## Supported Property Types

The factory automatically handles these property types:

| Type | Handling |
|------|----------|
| Scalar types (`string`, `int`, `bool`, etc.) | Direct value mapping |
| `ElasticId` / `ElasticIdInterface` | Created from document `_id` |
| `Date` / `DateTime` | Parsed from stored string format |
| `ValueInterface` implementations | Instantiated with stored value |
| `EntityInterface` (nested) | Recursively hydrated via `#[Entity]` attribute |
| `EntityCollectionInterface` | Items hydrated via `#[Collection]` attribute |
| `ElasticEntityCollection` | Referenced entities loaded via `#[ElasticCollection]` attribute |
| STI entities | Correct subclass instantiated via `#[STIEntity]` or `#[STIElasticEntity]` |
| Nullable types | `null` preserved when field is missing |
| Default values | Used when field is missing and property has default |

## Debugging Entity Hydration

When troubleshooting hydration issues, you can inspect the raw hit data:

```php
// Get raw search results
$query = new \Spameri\ElasticQuery\ElasticQuery();
$query->query()->must()->add(
    new \Spameri\ElasticQuery\Query\Term('_id', 'abc123')
);

$result = $getBy->execute($query, 'videos');
$hit = $result->hits()->collection()->current();

// Inspect available data
$id = $hit->id();                           // Document ID
$title = $hit->getValue('title');           // Simple field
$imdb = $hit->getValue('identification.imdb'); // Nested field (dot notation)
$source = $hit->source();                   // Entire document array

// Check if field exists
if ($hit->hasValue('optional_field')) {
    $value = $hit->getValue('optional_field');
}
```

## Entity Requirements

For proper hydration, entities must:

1. Accept all properties via constructor (factory uses named arguments)
2. Have `ElasticIdInterface $id` as a property (receives document `_id`)
3. Use mapping attributes (`#[Entity]`, `#[Collection]`, etc.) for complex types
4. Match constructor parameter names to document field names

See [Entity Class Guide](03_entity_class.md) for detailed entity structure requirements.

## Next Steps

- [EntityManager Guide](17_entity_manager.md) - How entities are loaded and persisted
- [Identity Map](21_identity_map.md) - Understanding entity caching
- [Mapping Attributes](18_mapping_attributes.md) - Configure entity property mapping
