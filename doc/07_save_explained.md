# Insert Explained

## Overview

This document explains how entities are converted to arrays and persisted to ElasticSearch.

## PrepareEntityArray

`\Spameri\Elastic\Model\Insert\PrepareEntityArray` is responsible for converting ElasticSearch entities to arrays that can be saved to ElasticSearch.

Configuration is done by implementing [interfaces](04_data_interfaces.md) - no annotations or neon configuration needed for basic persistence.

### prepare() Method

```php
public function prepare(
    \Spameri\Elastic\Entity\AbstractElasticEntity $entity,
    bool $hasSti = false,
): array
```

This method:
1. Gets entity variables via `entityVariables()`
2. Adds `entityClass` field if STI is enabled
3. Calls `iterateVariables()` to convert all properties

### iterateVariables() Method

This method accepts entity variables and converts them to an array based on their type.

## Property Types Handled

### 1. ElasticEntityInterface (Related Entities)

When a property implements `ElasticEntityInterface`:
- The related entity is persisted to its own index
- Only the entity's ID is stored in the parent document

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public readonly Person $director, // ElasticEntityInterface
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "director": "person-id-123"  // Only the ID
}
```

### 2. EntityInterface (Embedded Entities)

When a property implements `EntityInterface`:
- The entity is embedded directly in the parent document
- Properties are iterated recursively via `iterateVariables()`

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public readonly Technical $technical, // EntityInterface
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "technical": {
        "codec": "h264",
        "bitrate": 8000
    }
}
```

### 3. ValueInterface (Value Objects)

When a property implements `ValueInterface`:
- The raw value is extracted via `value()` method
- Stored as primitive type in ElasticSearch

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public readonly ImdbId $imdbId, // ValueInterface
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "imdbId": "tt0111161"  // The raw string value
}
```

### 4. EntityCollectionInterface

When a property implements `EntityCollectionInterface`:
- Each item is iterated and converted as `EntityInterface` (type 2)
- Stored as array of embedded objects
- Use `EntityCollection` directly - no custom collection classes needed

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        #[\Spameri\Elastic\Mapping\Collection]
        public readonly \Spameri\Elastic\Entity\Collection\EntityCollection $seasons,
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "seasons": [
        {"number": 1, "episodes": 10},
        {"number": 2, "episodes": 12}
    ]
}
```

### 5. ElasticEntityCollectionInterface

When a property implements `ElasticEntityCollectionInterface`:
- Each item is treated as `ElasticEntityInterface` (type 1)
- Each item is persisted to its own index
- Stored as array of IDs
- Use `ElasticEntityCollection` directly - no custom collection classes needed

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        #[\Spameri\Elastic\Mapping\ElasticCollection(class: Person::class)]
        public readonly \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $actors,
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "actors": ["person-1", "person-2", "person-3"]  // Array of IDs
}
```

### 6. Arrays of ValueInterface

When a property is an array of `ValueInterface` objects:
- Each item is iterated and converted as `ValueInterface` (type 3)
- Stored as array of primitive values
- Use typed arrays directly - no custom collection classes needed

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        /** @var array<Tag> */
        public readonly array $tags = [],
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "tags": ["action", "thriller", "drama"]  // Array of string values
}
```

### 7. Scalar Values

For `string`, `int`, `bool`, `float`, or `null`:
- Values are passed directly to the array

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public readonly string $title,
        public readonly int $year,
        public readonly bool $isPublished,
        public readonly ?string $description,
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "title": "The Matrix",
    "year": 1999,
    "isPublished": true,
    "description": null
}
```

### 8. Library DateTimeInterface

For `\Spameri\Elastic\Entity\DateTimeInterface`:
- `\Spameri\Elastic\Entity\Property\Date` → formatted as `Y-m-d`
- `\Spameri\Elastic\Entity\Property\DateTime` → formatted as `Y-m-d\TH:i:s`

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public readonly \Spameri\Elastic\Entity\Property\Date $releaseDate,
        public readonly \Spameri\Elastic\Entity\Property\DateTime $createdAt,
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "releaseDate": "1999-03-31",
    "createdAt": "2024-01-15T10:30:00"
}
```

### 9. Standard DateTime

For `\DateTime` or `\DateTimeImmutable`:
- Formatted as `Y-m-d\TH:i:s`

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        public readonly \DateTime $updatedAt,
    ) {
        parent::__construct($id);
    }
}

// Stored as:
{
    "updatedAt": "2024-01-15T14:30:00"
}
```

### 10. Unknown Types

If a property type doesn't match any of the above:
- An exception is thrown
- Check that your property implements the correct interface

## Complete Persistence Flow

```
EntityManager::persist($entity)
         │
         ▼
    PRE_PERSIST Event
         │
         ▼
    Insert::execute()
         │
         ▼
    IdentityMap::isChanged()
         │
    ┌────┴────┐
    │         │
 changed   unchanged
    │         │
    ▼         ▼
PrepareEntityArray  Return (skip save)
         │
         ▼
    iterateVariables()
         │
    ┌────┴────────────────┐
    │    For each property     │
    └─────────┬───────────┘
              │
    ┌─────────┴─────────┐
    │  Determine type   │
    └─────────┬─────────┘
              │
    ┌─────────▼─────────┐
    │  Convert to array │
    └─────────┬─────────┘
              │
              ▼
    ElasticSearch Client::index()
              │
              ▼
    POST_PERSIST Event
              │
              ▼
    POST_CREATE or POST_UPDATE
```

## STI (Single Table Inheritance)

When `hasSti` is true:
- The `entityClass` field is added with the fully qualified class name
- Child entity types can be distinguished when loading

```php
// With STI enabled:
{
    "entityClass": "App\\Model\\Entity\\Movie",
    "title": "The Matrix",
    "year": 1999
}
```

## Next Steps

- [Data Interfaces](04_data_interfaces.md) - Interface reference
- [Entity Class Guide](03_entity_class.md) - Building entities
- [EntityManager Guide](17_entity_manager.md) - Full persistence API
