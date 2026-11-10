# Data Interfaces

This document describes the core interfaces used to build entity structures in Spameri/Elastic.

## Entity Interfaces

### ElasticEntityInterface

```php
interface ElasticEntityInterface
{
    public function id(): \Spameri\Elastic\Entity\Property\ElasticIdInterface;
    public function entityVariables(): array;
}
```

**Purpose:** Base interface for ElasticSearch entities. Represents a document stored in its own ElasticSearch index.

**Key Points:**
- Each entity has a unique ID managed by ElasticSearch
- The `entityVariables()` method returns all properties for serialization
- Extend `AbstractElasticEntity` for the default implementation

**Example:**
```php
class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        public readonly string $title,
        public readonly int $year,
    ) {
        parent::__construct($id);
    }
}
```

---

### EntityInterface

```php
interface EntityInterface
{
    public function key(): string;
    public function entityVariables(): array;
}
```

**Purpose:** Interface for nested/embedded entities. These are stored as nested objects within a parent document, not in their own index.

**Key Points:**
- Has a `key()` method for identification within collections
- No ElasticSearch ID - these are not top-level documents
- Use for structured data that belongs to a parent entity

**Example:**
```php
class Technical implements \Spameri\Elastic\Entity\EntityInterface
{
    public function __construct(
        public readonly string $codec,
        public readonly int $bitrate,
    ) {}

    public function key(): string
    {
        return 'technical';
    }

    public function entityVariables(): array
    {
        return \get_object_vars($this);
    }
}
```

---

### ValueInterface

```php
interface ValueInterface
{
    public function value(): mixed;
}
```

**Purpose:** Interface for single-value objects that encapsulate validation and type safety.

**Key Points:**
- Wraps a single value (string, int, etc.)
- Can contain validation logic in constructor
- Prevents invalid data from entering your entities
- Recommended for all entity properties that need validation

**Example:**
```php
class Email implements \Spameri\Elastic\Entity\ValueInterface
{
    public function __construct(
        private readonly string $value,
    ) {
        if (!\filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email: ' . $value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
```

---

### STIElasticEntityInterface

```php
interface STIElasticEntityInterface
{
    // Marker interface - no methods
}
```

**Purpose:** Marker interface for Single Table Inheritance support. When implemented, allows storing multiple entity types in a single index.

**Key Points:**
- Implement on the parent entity class
- Child entities extend the parent
- Index stores `entityClass` field to identify concrete type
- Enable STI via `hasSti()` in index configuration

See [STI Guide](20_sti_guide.md) for detailed examples.

---

## Collection Interfaces

### ElasticEntityCollectionInterface

```php
interface ElasticEntityCollectionInterface extends \IteratorAggregate
{
    public function add(AbstractElasticEntity $elasticEntity): void;
    public function entity(ElasticIdInterface $id): AbstractElasticEntity|null;
    public function first(): AbstractElasticEntity|null;
    public function remove(ElasticIdInterface $id): void;
    public function isValue(ElasticIdInterface $id): bool;
    public function count(): int;
    public function keys(): array;
    public function clear(): void;
    public function initialized(): bool;
    public function elasticIds(): array;
    public function sort(SortField $sortField, string $type): void;
}
```

**Purpose:** Collection of `ElasticEntityInterface` objects. Used for relationships between entities stored in different indexes.

**Key Points:**
- Items are stored by reference (only IDs in parent document)
- Supports lazy loading via `initialized()`
- Each item is persisted to its own index
- Use `#[ElasticCollection]` attribute on property

**Example:**
```php
// In Video entity:
#[\Spameri\Elastic\Mapping\ElasticCollection(class: Person::class)]
private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $actors;
```

---

### EntityCollectionInterface

```php
interface EntityCollectionInterface extends \IteratorAggregate
{
    public function add(EntityInterface $entity): void;
    public function entity(string $key): EntityInterface|null;
    public function remove(string|int $key): void;
    public function isValue(string $key): bool;
    public function count(): int;
    public function keys(): array;
    public function clear(): void;
    public function sort(SortField $sortField, string $type): void;
}
```

**Purpose:** Collection of `EntityInterface` objects. Used for nested arrays of objects within a document.

**Key Points:**
- Items are stored directly in the parent document
- Items identified by `key()` method
- No lazy loading - all data embedded
- Use `#[Collection]` attribute on property

**Example:**
```php
// In Video entity:
#[\Spameri\Elastic\Mapping\Collection]
private \Spameri\Elastic\Entity\Collection\EntityCollection $seasons;
```

---

### ValueCollectionInterface (Deprecated)

```php
interface ValueCollectionInterface extends \IteratorAggregate
{
    // Minimal interface - just IteratorAggregate
}
```

**Purpose:** Collection of `ValueInterface` objects. Used for arrays of simple values.

**Recommended:** Use typed arrays instead of custom collection classes.

**Key Points:**
- Items are stored as array in the document
- Each item implements `ValueInterface`
- Useful for lists of tags, categories, etc.
- **Prefer using `array<ValueInterface>` with PHPDoc annotations**

**Example:**
```php
// In your entity:
/** @var array<Tag> */
private array $tags;
```

---

## Date Interfaces

### DateTimeInterface

```php
interface DateTimeInterface
{
    // Marker for library's date classes
}
```

**Purpose:** Marker interface for date/time values with consistent ElasticSearch formatting.

**Available Implementations:**

| Class | Format | Example |
|-------|--------|---------|
| `\Spameri\Elastic\Entity\Property\Date` | `Y-m-d` | `2024-01-15` |
| `\Spameri\Elastic\Entity\Property\DateTime` | `Y-m-d\TH:i:s` | `2024-01-15T14:30:00` |

**Example:**
```php
public function __construct(
    // ...
    public readonly \Spameri\Elastic\Entity\Property\DateTime $createdAt,
    public readonly \Spameri\Elastic\Entity\Property\Date|null $releaseDate,
) {}
```

---

## Interface Hierarchy

```
ElasticEntityInterface
└── AbstractElasticEntity (abstract class)
    └── Your entities (Video, Person, etc.)

EntityInterface
└── Your nested entities (Technical, Story, etc.)

ValueInterface
└── Your value objects (Email, ImdbId, etc.)

Collections:
├── ElasticEntityCollectionInterface → ElasticEntityCollection (use directly)
├── EntityCollectionInterface → EntityCollection (use directly)
└── ValueCollectionInterface → array<ValueInterface> (use typed arrays)
```

---

## When to Use Each Interface

| Scenario | Interface/Type | Storage |
|----------|----------------|---------|
| Main document in its own index | `ElasticEntityInterface` | Own index |
| Nested object within document | `EntityInterface` | Embedded |
| Single validated value | `ValueInterface` | As primitive |
| References to other documents | `ElasticEntityCollection` | IDs only |
| Array of nested objects | `EntityCollection` | Embedded array |
| Array of simple values | `array<ValueInterface>` | Primitive array |

---

## Next Steps

- [Entity Class Guide](03_entity_class.md) - Complete entity examples
- [Mapping Attributes](18_mapping_attributes.md) - How to annotate properties
- [Value Objects Guide](22_value_objects.md) - Creating value objects
