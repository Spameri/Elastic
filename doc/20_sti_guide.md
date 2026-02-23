# Single Table Inheritance (STI) Guide

## Overview

Single Table Inheritance (STI) allows you to store multiple entity types in a single ElasticSearch index. This is useful when you have entities that share common properties but also have type-specific fields.

## When to Use STI

**Use STI when:**
- Entities share a significant portion of their structure
- You need to query across all types in a single search
- The number of type-specific fields is limited
- You want simplified index management

**Avoid STI when:**
- Entity types are fundamentally different
- Each type has many unique fields
- You need different index settings per type
- Performance is critical and types have vastly different query patterns

## Basic Setup

### 1. Create the Parent Entity

The parent entity implements `STIElasticEntityInterface`:

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity;

abstract class Content extends \Spameri\Elastic\Entity\AbstractElasticEntity
    implements \Spameri\Elastic\Entity\STIElasticEntityInterface
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        public readonly string $title,
        public readonly string $description,
        public readonly \Spameri\Elastic\Entity\Property\DateTime $createdAt,
    ) {
        parent::__construct($id);
    }
}
```

### 2. Create Child Entities

Child entities extend the parent and add type-specific properties:

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity;

class Article extends Content
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        string $title,
        string $description,
        \Spameri\Elastic\Entity\Property\DateTime $createdAt,
        public readonly string $author,
        public readonly int $wordCount,
    ) {
        parent::__construct($id, $title, $description, $createdAt);
    }
}
```

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity;

class Video extends Content
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        string $title,
        string $description,
        \Spameri\Elastic\Entity\Property\DateTime $createdAt,
        public readonly int $duration,
        public readonly string $format,
    ) {
        parent::__construct($id, $title, $description, $createdAt);
    }
}
```

### 3. Create Index Configuration

The index configuration specifies all entity classes that share the index:

```php
<?php declare(strict_types = 1);

namespace App\Model\Settings;

class ContentMapping implements \Spameri\Elastic\Settings\IndexConfigInterface
{
    public function __construct(
        private readonly string $indexName = 'content',
    ) {}

    public function provide(): \Spameri\ElasticQuery\Mapping\Settings
    {
        $settings = new \Spameri\ElasticQuery\Mapping\Settings($this->indexName);

        // Common fields
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'title',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
            )
        );
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'description',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_TEXT
            )
        );
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'createdAt',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_DATE
            )
        );

        // STI discriminator field (automatically managed)
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'entityClass',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        );

        // Article-specific fields
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'author',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        );
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'wordCount',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_INTEGER
            )
        );

        // Video-specific fields
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'duration',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_INTEGER
            )
        );
        $settings->addMappingField(
            new \Spameri\ElasticQuery\Mapping\Settings\Mapping\Field(
                'format',
                \Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPE_KEYWORD
            )
        );

        return $settings;
    }

    /**
     * Return ALL entity classes that use this index
     */
    public function entityClass(): array
    {
        return [
            \App\Model\Entity\Content::class,
            \App\Model\Entity\Article::class,
            \App\Model\Entity\Video::class,
        ];
    }

    public function indexName(): string
    {
        return $this->indexName;
    }
}
```

### 4. Register in Configuration

```neon
spameriElasticSearch:
    entities:
        - App\Model\Entity\Content
        - App\Model\Entity\Article
        - App\Model\Entity\Video

services:
    - App\Model\Settings\ContentMapping
```

## How STI Works

### Storage

When persisting an entity, the library:
1. Detects that the entity implements `STIElasticEntityInterface`
2. Adds the `entityClass` field with the fully qualified class name
3. Stores all properties in the shared index

**Document Example:**
```json
{
    "_id": "abc123",
    "_source": {
        "entityClass": "App\\Model\\Entity\\Article",
        "title": "Introduction to ElasticSearch",
        "description": "A comprehensive guide...",
        "createdAt": "2024-01-15T10:30:00",
        "author": "John Doe",
        "wordCount": 2500
    }
}
```

### Retrieval

When retrieving entities:
1. The factory reads the `entityClass` field
2. Instantiates the correct concrete class
3. Returns the properly typed entity

```php
// Query returns mixed types
$contents = $entityManager->findAll(Content::class);

foreach ($contents as $content) {
    if ($content instanceof Article) {
        echo "Article by {$content->author}";
    } elseif ($content instanceof Video) {
        echo "Video: {$content->duration} seconds";
    }
}
```

## Querying STI Entities

### Query All Types

```php
// Find all content regardless of type
$allContent = $entityManager->findAll(Content::class);
```

### Query Specific Type

```php
// Find only articles
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->query()->addMust(
    new \Spameri\ElasticQuery\Query\Term(
        'entityClass',
        Article::class
    )
);

$articles = $entityManager->findBy(Content::class, $elasticQuery);
```

### Query with Type-Specific Fields

```php
// Find videos longer than 10 minutes
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->query()->addMust(
    new \Spameri\ElasticQuery\Query\Term('entityClass', Video::class)
);
$elasticQuery->query()->addMust(
    new \Spameri\ElasticQuery\Query\Range('duration', 600, null)
);

$longVideos = $entityManager->findBy(Content::class, $elasticQuery);
```

## Nested STI Entities

STI can also be used for nested entities (not just root documents).

### Using #[STIEntity] Attribute

For embedded entities that use STI:

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity;

class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        public readonly string $title,
        #[\Spameri\Elastic\Mapping\STIEntity]
        public readonly MediaInfo $mediaInfo,
    ) {
        parent::__construct($id);
    }
}
```

The `#[STIEntity]` attribute tells the factory to read the `entityClass` field from the nested object and instantiate the correct type.

### STI Entity Interface for Nested Objects

```php
<?php declare(strict_types = 1);

namespace App\Model\Entity;

abstract class MediaInfo implements \Spameri\Elastic\Entity\EntityInterface,
    \Spameri\Elastic\Entity\STIEntityInterface
{
    public function key(): string
    {
        return 'mediaInfo';
    }

    abstract public function entityVariables(): array;
}

class AudioInfo extends MediaInfo
{
    public function __construct(
        public readonly int $bitrate,
        public readonly string $codec,
    ) {}

    public function entityVariables(): array
    {
        return \get_object_vars($this);
    }
}

class VideoInfo extends MediaInfo
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public readonly string $codec,
    ) {}

    public function entityVariables(): array
    {
        return \get_object_vars($this);
    }
}
```

## STI in Collections

### Using #[STIElasticEntity] for Related Entities

When you have a collection of STI entities that are stored in their own index:

```php
class Playlist extends \Spameri\Elastic\Entity\AbstractElasticEntity
{
    public function __construct(
        \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
        public readonly string $name,
        #[\Spameri\Elastic\Mapping\STIElasticEntity]
        #[\Spameri\Elastic\Mapping\ElasticCollection(Content::class)]
        public readonly ContentCollection $items,
    ) {
        parent::__construct($id);
    }
}
```

## Best Practices

### 1. Keep Inheritance Shallow

```php
// Good: One level of inheritance
Content (abstract)
├── Article
├── Video
└── Podcast

// Avoid: Deep inheritance hierarchies
Content (abstract)
└── Media (abstract)
    └── Video (abstract)
        └── ShortVideo
```

### 2. Document Type-Specific Fields

Add comments explaining which fields belong to which types:

```php
// Common fields (all types)
$settings->addMappingField(new Field('title', TYPE_TEXT));
$settings->addMappingField(new Field('createdAt', TYPE_DATE));

// Article fields
$settings->addMappingField(new Field('author', TYPE_KEYWORD));

// Video fields
$settings->addMappingField(new Field('duration', TYPE_INTEGER));
```

### 3. Use Aggregations for Type Distribution

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->aggregation()->add(
    new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
        'by-type',
        null,
        new \Spameri\ElasticQuery\Aggregation\Term('entityClass')
    )
);

$result = $entityManager->aggregate(Content::class, $elasticQuery);
// Returns count of each entity type in the index
```

### 4. Type Guards in Code

Always use type guards when working with STI collections:

```php
foreach ($contents as $content) {
    match (true) {
        $content instanceof Article => $this->processArticle($content),
        $content instanceof Video => $this->processVideo($content),
        default => throw new \UnexpectedValueException(
            'Unknown content type: ' . $content::class
        ),
    };
}
```

## Troubleshooting

### Entity Class Not Found

**Problem:** Factory throws exception about unknown entity class.

**Solution:** Ensure all child classes are registered in configuration:
```neon
spameriElasticSearch:
    entities:
        - App\Model\Entity\Content  # Parent
        - App\Model\Entity\Article  # Child 1
        - App\Model\Entity\Video    # Child 2
```

### Missing entityClass Field

**Problem:** Retrieved entities are always the base type.

**Solution:** The `entityClass` field must be in the mapping:
```php
$settings->addMappingField(
    new Field('entityClass', TYPE_KEYWORD)
);
```

### Type-Specific Field Missing

**Problem:** Null values for type-specific fields.

**Solution:** Ensure the mapping includes all fields from all types, even if some documents won't have them.

## Performance Considerations

- STI indexes may be larger due to sparse fields
- Queries for specific types should include the `entityClass` filter
- Consider separate indexes if types have vastly different access patterns
- Use sparse field optimization in ElasticSearch for unused fields

## Next Steps

- [Entity Class Guide](03_entity_class.md) - Basic entity structure
- [Mapping Attributes](18_mapping_attributes.md) - All available attributes
- [EntityManager Guide](17_entity_manager.md) - Working with entities
