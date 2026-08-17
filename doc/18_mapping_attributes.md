# Mapping Attributes Reference

## Overview

Mapping attributes are PHP 8 attributes used to control how the `EntityFactory` hydrates entities from ElasticSearch. They tell the factory how to handle complex property types that can't be automatically resolved.

**Location:** `src/Mapping/`

**Available Attributes:**
- `#[Entity]` - Nested entity objects
- `#[Collection]` - Collection of nested entities
- `#[ElasticCollection]` - Collection of related ElasticSearch entities
- `#[STIEntity]` - Single Table Inheritance nested entity
- `#[STIElasticEntity]` - Single Table Inheritance ElasticSearch entity reference
- `#[Ignored]` - Skip property during hydration

---

## When to Use Attributes

### Automatic Hydration (No Attribute Needed)

The factory automatically handles:
- Scalar types (`string`, `int`, `float`, `bool`)
- `null` values (when property is nullable)
- `ElasticIdInterface` properties (first constructor parameter)
- `Date` and `DateTime` properties
- `ValueInterface` implementations (value objects)

### Requires Attributes

You **must** use attributes for:
- Nested entity objects **when the type is an interface** (use `#[Entity]`)
- Entity collections (`EntityCollectionInterface`, `ElasticEntityCollectionInterface`)
- Single Table Inheritance relationships
- Properties to ignore during hydration

**Note:** When a property has a concrete class type (not an interface), no `#[Entity]` attribute is needed - the EntityFactory determines the class from the type hint.

---

## #[Entity] Attribute

**Purpose:** Specifies the concrete class for a nested entity when the property type is an interface or abstract class.

**Use For:** Properties where the type hint is an interface and the EntityFactory cannot determine which concrete class to instantiate.

**When NOT Needed:** If the property type is a concrete class, the EntityFactory reads the class from the type hint automatically - no attribute required.

### Syntax

```php
#[\Spameri\Elastic\Mapping\Entity(class: ConcreteClass::class)]
private SomeInterface $propertyName
```

### Parameters

- `class` (string, required) - Fully qualified class name of the concrete entity to instantiate

### Example: When #[Entity] IS Needed (Interface Type)

```php
<?php

namespace App\Entity;

// Interface for polymorphic media sources
interface MediaSourceInterface extends \Spameri\Elastic\Entity\EntityInterface {}

class StreamingSource implements MediaSourceInterface { /* ... */ }
class DownloadSource implements MediaSourceInterface { /* ... */ }

class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $title,

        // Type is interface - must specify concrete class
        #[\Spameri\Elastic\Mapping\Entity(class: StreamingSource::class)]
        private MediaSourceInterface $source,
    ) {
        parent::__construct($id);
    }
}
```

### Example: When #[Entity] is NOT Needed (Concrete Type)

```php
<?php

namespace App\Entity;

class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $title,

        // Type is concrete class - NO attribute needed
        private Technical $technical,
        private Story $story,
    ) {
        parent::__construct($id);
    }
}

// Nested entity with concrete type
class Technical implements \Spameri\Elastic\Entity\EntityInterface
{
    public function __construct(
        private int $duration,
        private string $resolution,
        private string $codec,
    ) {}

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }

    public function key(): string
    {
        return md5(implode('_', $this->entityVariables()));
    }
}
```

### ElasticSearch Document Structure

```json
{
    "_id": "video123",
    "_source": {
        "title": "Sample Video",
        "technical": {
            "duration": 3600,
            "resolution": "1920x1080",
            "codec": "h264"
        }
    }
}
```

### How It Works

1. Factory sees `#[Entity]` attribute on `$technical` property
2. Reads the `class` parameter: `Technical::class`
3. Recursively calls `resolveProperties()` for `Technical` class
4. Uses dot notation to access nested fields: `technical.duration`, `technical.resolution`, etc.
5. Constructs `Technical` object with resolved properties
6. Passes constructed object to `Video` constructor

### Special Case: ElasticId

For ElasticId properties (not the main entity ID):

```php
class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,

        #[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
        private \Spameri\Elastic\Entity\Property\ElasticId $referenceId,
    ) {
        $this->id = $id;
    }
}
```

The factory will use the document's `_id` field for this property instead of nested data.

---

## #[Collection] Attribute

**Purpose:** Marks a property as a collection of nested entities stored inline as an array.

**Use For:** Properties implementing `EntityCollectionInterface` containing entities that are part of the same document.

### Syntax

```php
#[\Spameri\Elastic\Mapping\Collection]
private YourCollectionType $propertyName
```

### Parameters

None - the collection class determines the entity type.

### Example: Nested Entity Collection

```php
<?php

namespace App\Entity;

class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $title,

        #[\Spameri\Elastic\Mapping\Collection]
        private \Spameri\Elastic\Entity\Collection\EntityCollection $seasons,
    ) {
        parent::__construct($id);
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}

// Use EntityCollection directly - no custom collection class needed

// Individual entity
class Season implements \Spameri\Elastic\Entity\EntityInterface
{
    public function __construct(
        private int $number,
        private int $episodeCount,
        private string $releaseYear,
    ) {}

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }

    public function key(): string
    {
        return (string) $this->number;
    }
}
```

### ElasticSearch Document Structure

```json
{
    "_id": "video123",
    "_source": {
        "title": "TV Show",
        "seasons": [
            {
                "entityClass": "App\\Entity\\Season",
                "number": 1,
                "episodeCount": 10,
                "releaseYear": "2020"
            },
            {
                "entityClass": "App\\Entity\\Season",
                "number": 2,
                "episodeCount": 12,
                "releaseYear": "2021"
            }
        ]
    }
}
```

### How It Works

1. Factory sees `#[Collection]` attribute
2. Creates empty collection instance: `new SeasonCollection()`
3. Iterates over array in `seasons` field
4. For each item:
   - Reads `entityClass` field to determine concrete class
   - Resolves properties for that class using dot notation: `seasons.0.number`, `seasons.0.episodeCount`, etc.
   - Constructs entity instance
   - Calls `$collection->add($entity)`
5. Marks collection as existing in ChangeSet
6. Passes collection to parent constructor

**Note:** Each collection item must have an `entityClass` field indicating its type.

---

## #[ElasticCollection] Attribute

**Purpose:** Marks a property as a collection of related ElasticSearch entities stored in separate documents.

**Use For:** Properties implementing `ElasticEntityCollectionInterface` that reference other indexed documents.

### Syntax

```php
#[\Spameri\Elastic\Mapping\ElasticCollection(class: RelatedEntityClass::class)]
private YourElasticCollectionType $propertyName
```

### Parameters

- `class` (string, required) - Fully qualified class name of the related entity

### Example: Related Entity Collection

```php
<?php

namespace App\Entity;

class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $title,

        #[\Spameri\Elastic\Mapping\ElasticCollection(class: Person::class)]
        private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $cast,

        private \Spameri\Elastic\EntityManager $entityManager,
    ) {
        parent::__construct($id);
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}

// Use ElasticEntityCollection directly - no custom collection class needed

// Related entity (in separate index)
class Person extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $name,
        private string $role,
    ) {
        $this->id = $id;
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}
```

### ElasticSearch Document Structure

**Video Document:**
```json
{
    "_id": "video123",
    "_source": {
        "title": "Movie Name",
        "cast": ["person456", "person789", "person101"]
    }
}
```

**Person Documents (separate index):**
```json
{
    "_id": "person456",
    "_source": {
        "name": "John Doe",
        "role": "Actor"
    }
}
```

### How It Works

1. Factory sees `#[ElasticCollection]` attribute with `class: Person::class`
2. Creates collection instance: `new PeopleCollection($entityManager, Person::class)`
3. Reads array of IDs from `cast` field: `["person456", "person789", "person101"]`
4. For each ID:
   - Calls `$entityManager->find($id, Person::class)`
   - Loads full `Person` entity from ElasticSearch
   - Adds to collection via `$collection->add($person)`
5. Collection is lazy-loaded (entities fetched only when accessed)

### Persistence Behavior

When saving the parent entity:
- Only **IDs** are stored in the parent document
- Related entities are **automatically persisted** to their own index
- Changes to related entities are saved when parent is saved

**Example:**
```php
$person = new Person(
    new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
    'Jane Smith',
    'Director'
);

$video->cast->add($person);

// Saving video also saves person to Person index
$entityManager->persist($video);

// Result: video document contains person ID, person stored separately
```

---

## #[STIEntity] Attribute

**Purpose:** Marks a property as a Single Table Inheritance nested entity where the concrete class varies.

**Use For:** Polymorphic nested entities that can be different subclasses but are stored inline.

### Syntax

```php
#[\Spameri\Elastic\Mapping\STIEntity]
private ParentEntityType $propertyName
```

### Parameters

None - concrete class is read from `entityClass` field in document.

### Example: Polymorphic Nested Entity

```php
<?php

namespace App\Entity;

class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $title,

        #[\Spameri\Elastic\Mapping\STIEntity]
        private MediaSource $source,
    ) {
        $this->id = $id;
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}

// Base class
abstract class MediaSource implements \Spameri\Elastic\Entity\EntityInterface
{
    abstract public function entityVariables(): array;
    abstract public function key(): string;
}

// Concrete implementation 1
class StreamingSource extends MediaSource
{
    public function __construct(
        private string $streamUrl,
        private string $quality,
    ) {}

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }

    public function key(): string
    {
        return md5($this->streamUrl);
    }
}

// Concrete implementation 2
class DownloadSource extends MediaSource
{
    public function __construct(
        private string $downloadUrl,
        private int $fileSize,
    ) {}

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }

    public function key(): string
    {
        return md5($this->downloadUrl);
    }
}
```

### ElasticSearch Document Structure

**Streaming Source:**
```json
{
    "_id": "video123",
    "_source": {
        "title": "Streaming Movie",
        "source": {
            "entityClass": "App\\Entity\\StreamingSource",
            "streamUrl": "https://cdn.example.com/stream",
            "quality": "1080p"
        }
    }
}
```

**Download Source:**
```json
{
    "_id": "video456",
    "_source": {
        "title": "Download Movie",
        "source": {
            "entityClass": "App\\Entity\\DownloadSource",
            "downloadUrl": "https://cdn.example.com/file.mp4",
            "fileSize": 1073741824
        }
    }
}
```

### How It Works

1. Factory sees `#[STIEntity]` attribute
2. Reads `entityClass` field from nested object: `"App\\Entity\\StreamingSource"`
3. Uses that class instead of property type hint
4. Resolves properties for concrete class
5. Constructs correct subclass instance
6. Type hint must be parent class or interface

**Key Difference from `#[Entity]`:**
- `#[Entity]` - Always creates same class (specified in attribute)
- `#[STIEntity]` - Creates different classes based on `entityClass` field

---

## #[STIElasticEntity] Attribute

**Purpose:** Marks a property as a Single Table Inheritance reference to another ElasticSearch entity.

**Use For:** Polymorphic relationships where the related entity can be different subclasses.

### Syntax

```php
#[\Spameri\Elastic\Mapping\STIElasticEntity]
private ParentEntityType $propertyName
```

### Parameters

None - concrete class and ID are read from document fields.

### Example: Polymorphic Entity Reference

```php
<?php

namespace App\Entity;

class Notification extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $message,
        private \DateTime $sentAt,

        #[\Spameri\Elastic\Mapping\STIElasticEntity]
        private User $recipient,

        private \Spameri\Elastic\EntityManager $entityManager,
    ) {
        $this->id = $id;
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}

// Base user class
abstract class User extends \Spameri\Elastic\Entity\AbstractElasticEntity
    implements \Spameri\Elastic\Entity\STIElasticEntityInterface
{
    // Common user properties
}

// Concrete implementation 1
class AdminUser extends User
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $username,
        private array $permissions,
    ) {
        $this->id = $id;
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}

// Concrete implementation 2
class RegularUser extends User
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $email,
        private string $name,
    ) {
        $this->id = $id;
    }

    public function entityVariables(): array
    {
        return get_object_vars($this);
    }
}
```

### ElasticSearch Document Structure

**Notification Document:**
```json
{
    "_id": "notif123",
    "_source": {
        "message": "Welcome!",
        "sentAt": "2025-01-15T10:30:00",
        "recipient": {
            "entityClass": "App\\Entity\\AdminUser",
            "entityId": "user456"
        }
    }
}
```

**Referenced User Document (separate index):**
```json
{
    "_id": "user456",
    "_source": {
        "entityClass": "App\\Entity\\AdminUser",
        "username": "admin",
        "permissions": ["read", "write", "delete"]
    }
}
```

### How It Works

1. Factory sees `#[STIElasticEntity]` attribute
2. Reads `entityClass` and `entityId` from nested object
3. Calls `$entityManager->find($entityId, $entityClass)`
4. Loads correct subclass from ElasticSearch
5. Handles circular references (prevents infinite loops)

### Circular Reference Handling

If the related entity is currently being created (circular dependency):
1. Factory creates temporary placeholder object
2. Adds to `uninitializedEntityList`
3. After parent entity completes, fills in actual reference
4. Prevents infinite recursion

---

## #[Ignored] Attribute

**Purpose:** Tells the factory to skip a property during hydration.

**Use For:**
- Runtime-only properties
- Computed properties
- Properties with default values that aren't stored in ElasticSearch

### Syntax

```php
#[\Spameri\Elastic\Mapping\Ignored]
private mixed $propertyName
```

### Parameters

None

### Example: Ignored Properties

```php
<?php

namespace App\Entity;

class Product extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        private string $name,
        private float $price,

        // Not stored in ElasticSearch, computed at runtime
        #[\Spameri\Elastic\Mapping\Ignored]
        private ?float $discountedPrice = null,

        // Service injection, not data
        #[\Spameri\Elastic\Mapping\Ignored]
        private ?\App\Service\PriceCalculator $calculator = null,
    ) {
        $this->id = $id;
    }

    public function getDiscountedPrice(): float
    {
        if ($this->discountedPrice === null && $this->calculator !== null) {
            $this->discountedPrice = $this->calculator->calculate($this->price);
        }

        return $this->discountedPrice ?? $this->price;
    }

    public function setCalculator(\App\Service\PriceCalculator $calculator): void
    {
        $this->calculator = $calculator;
    }

    public function entityVariables(): array
    {
        // Only return storable properties
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
        ];
    }
}
```

### ElasticSearch Document Structure

```json
{
    "_id": "prod123",
    "_source": {
        "name": "Product Name",
        "price": 99.99
    }
}
```

**Note:** `discountedPrice` and `calculator` are NOT in document and NOT hydrated.

### Use Cases

1. **Computed Properties:**
   ```php
   #[\Spameri\Elastic\Mapping\Ignored]
   private ?string $fullName = null;

   public function getFullName(): string
   {
       return $this->firstName . ' ' . $this->lastName;
   }
   ```

2. **Service Dependencies:**
   ```php
   #[\Spameri\Elastic\Mapping\Ignored]
   private ?LoggerInterface $logger = null;
   ```

3. **Cache Properties:**
   ```php
   #[\Spameri\Elastic\Mapping\Ignored]
   private ?array $cachedResults = null;
   ```

4. **Legacy Properties:**
   ```php
   // Old field no longer stored but kept for BC
   #[\Spameri\Elastic\Mapping\Ignored]
   private ?string $deprecated = null;
   ```

---

## Attribute Decision Tree

Use this flowchart to choose the right attribute:

```
Is the property in ElasticSearch?
├─ NO → Use #[Ignored]
└─ YES ↓

Is it a collection?
├─ YES ↓
│   └─ Are items in separate index?
│       ├─ YES → Use #[ElasticCollection(class: Item::class)]
│       └─ NO → Use #[Collection]
│
└─ NO ↓

Is it a nested object?
├─ YES ↓
│   └─ Can it be different classes (polymorphic)?
│       ├─ YES ↓
│       │   └─ Is it in separate index?
│       │       ├─ YES → Use #[STIElasticEntity]
│       │       └─ NO → Use #[STIEntity]
│       │
│       └─ NO ↓
│           └─ Is property type an interface?
│               ├─ YES → Use #[Entity(class: ConcreteClass::class)]
│               └─ NO → No attribute needed (type hint is sufficient)
│
└─ NO → No attribute needed (automatic hydration)
```

---

## Common Patterns

### Pattern 1: Deeply Nested Entities

No `#[Entity]` attribute needed when using concrete class types:

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,

        // Concrete type - no attribute needed
        private Story $story,
    ) {
        parent::__construct($id);
    }
}

class Story implements EntityInterface
{
    public function __construct(
        private string $plot,

        // Concrete type - no attribute needed
        private Genre $genre,
    ) {}

    // ...
}

class Genre implements EntityInterface
{
    public function __construct(
        private string $name,
        private string $category,
    ) {}

    // ...
}
```

**Document:**
```json
{
    "story": {
        "plot": "...",
        "genre": {
            "name": "Action",
            "category": "Entertainment"
        }
    }
}
```

---

### Pattern 2: Mixed Collections

```php
class Video extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,

        // Inline episodes - use EntityCollection directly
        #[Collection]
        private \Spameri\Elastic\Entity\Collection\EntityCollection $episodes,

        // Referenced actors in separate index - use ElasticEntityCollection directly
        #[ElasticCollection(class: Person::class)]
        private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $actors,

        private EntityManager $entityManager,
    ) {
        parent::__construct($id);
    }
}
```

---

### Pattern 3: Optional Nested Entity

No `#[Entity]` needed - concrete type with nullable:

```php
class Product extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        private string $name,

        // Nullable concrete type - no attribute needed
        private ?Discount $discount = null,
    ) {
        parent::__construct($id);
    }
}
```

**With Discount:**
```json
{
    "name": "Product",
    "discount": {
        "percentage": 20,
        "validUntil": "2025-12-31"
    }
}
```

**Without Discount:**
```json
{
    "name": "Product",
    "discount": null
}
```

---

### Pattern 4: Service Injection with Ignored

```php
class Product extends AbstractElasticEntity
{
    public function __construct(
        ElasticIdInterface $id,
        private string $name,
        private float $price,

        #[Ignored]
        private ?TaxCalculator $taxCalculator = null,
    ) {
        $this->id = $id;
    }

    public function setTaxCalculator(TaxCalculator $calculator): void
    {
        $this->taxCalculator = $calculator;
    }

    public function getTotalPrice(): float
    {
        if ($this->taxCalculator === null) {
            return $this->price;
        }

        return $this->taxCalculator->calculate($this->price);
    }
}
```

---

## Troubleshooting

### Error: "Entity to create does not exist"

**Cause:** `entityClass` field references non-existent class.

**Solution:**
1. Check class name spelling in document
2. Verify class is autoloadable
3. Ensure full namespace is stored: `App\\Entity\\MyClass`

---

### Error: Circular reference / infinite loop

**Cause:** Two entities reference each other with `#[STIElasticEntity]`.

**Solution:** The factory handles this automatically via `creatingEntityList`. If you still see errors:
1. Check that EntityManager is injected into entity constructor
2. Verify STI entities implement `STIElasticEntityInterface`

---

### Error: Property not hydrated

**Cause:** Missing attribute or wrong attribute type.

**Solution:**
1. Verify property is not `#[Ignored]`
2. Check attribute matches property type:
   - Collection → `#[Collection]` or `#[ElasticCollection]`
   - Nested object → `#[Entity]` or `#[STIEntity]`
3. Ensure attribute class name is fully qualified

---

### Error: Wrong class instantiated

**Cause:** Using `#[Entity]` instead of `#[STIEntity]` for polymorphic property.

**Solution:** Change to `#[STIEntity]` and ensure document has `entityClass` field.

---

## Best Practices

### 1. Use Fully Qualified Class Names

```php
// Good
#[Entity(class: \App\Entity\Video\Technical::class)]

// Bad - may not work depending on imports
#[Entity(class: Technical::class)]
```

### 2. Match Attribute to Property Type

```php
// Good - use concrete collection classes directly
#[Collection]
private \Spameri\Elastic\Entity\Collection\EntityCollection $items;

#[ElasticCollection(class: Person::class)]
private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $people;

// Bad - mismatch will cause errors
#[Collection]
private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $items; // Wrong!
```

### 3. Store entityClass for Collections

When persisting collections, always include `entityClass`:

```php
public function entityVariables(): array
{
    return [
        'entityClass' => static::class,
        // ... other properties
    ];
}
```

### 4. Inject EntityManager for ElasticCollection

```php
// Required for lazy loading
public function __construct(
    ElasticIdInterface $id,

    #[ElasticCollection(class: Person::class)]
    private \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $cast,

    private EntityManager $entityManager, // ← Required
) {
    parent::__construct($id);
}
```

### 5. Use Ignored for Non-Data Properties

```php
#[Ignored]
private ?ServiceInterface $service = null;

#[Ignored]
private array $runtimeCache = [];
```

---

## See Also

- [Entity Class](03_entity_class.md) - Entity structure and interfaces
- [Entity Factory](11_entity_factory.md) - How factory uses attributes
- [Data Interfaces](04_data_interfaces.md) - Entity and collection interfaces
- [Model Services](14_model_services.md) - EntityManager operations
