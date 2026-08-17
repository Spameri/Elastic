# Basic Get

## Description

Retrieve a single entity by its ElasticSearch ID. This is the fastest way to fetch a known document.

## Using EntityManager (Recommended)

```php
// Get by ID - returns entity or null
$video = $entityManager->find(
    Video::class,
    new \Spameri\Elastic\Entity\Property\ElasticId('abc123'),
);

if ($video === null) {
    throw new \RuntimeException('Video not found');
}

echo $video->title();
```

### Method Signature

```php
public function find(
    string $entityClass,
    \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
): \Spameri\Elastic\Entity\AbstractElasticEntity|null
```

**Parameters:**
- `$entityClass` - Fully qualified class name of the entity
- `$id` - ElasticSearch document ID wrapped in `ElasticId`

**Returns:**
- The entity instance if found
- `null` if document doesn't exist

## Creating ElasticId

The ID must be wrapped in an `ElasticId` object:

```php
// From string
$id = new \Spameri\Elastic\Entity\Property\ElasticId('abc123');

// From variable
$id = new \Spameri\Elastic\Entity\Property\ElasticId($idFromRequest);

// Empty ID throws exception
$id = new \Spameri\Elastic\Entity\Property\ElasticId(''); // InvalidArgumentException
```

## Identity Map Caching

The EntityManager uses an Identity Map to cache entities:

```php
// First call - fetches from ElasticSearch
$video1 = $entityManager->find(Video::class, new ElasticId('abc123'));

// Second call - returns cached instance (same object!)
$video2 = $entityManager->find(Video::class, new ElasticId('abc123'));

$video1 === $video2; // true - same instance
```

This ensures:
- No duplicate queries for the same entity
- Reference integrity across your application
- Reduced ElasticSearch load

## Error Handling

```php
try {
    $video = $entityManager->find(
        Video::class,
        new \Spameri\Elastic\Entity\Property\ElasticId($id),
    );

    if ($video === null) {
        // Handle not found
        throw new NotFoundException('Video not found: ' . $id);
    }
} catch (\Spameri\Elastic\Exception\ElasticSearch $e) {
    // Handle ElasticSearch errors (connection, index missing, etc.)
    $this->logger->error('ElasticSearch error', ['exception' => $e]);
}
```

## Complete Example

```php
<?php declare(strict_types = 1);

namespace App\Presenter;

class VideoPresenter extends \Nette\Application\UI\Presenter
{
    public function __construct(
        private readonly \Spameri\Elastic\EntityManager $entityManager,
    ) {
        parent::__construct();
    }

    public function actionDetail(string $id): void
    {
        $video = $this->entityManager->find(
            \App\Model\Entity\Video::class,
            new \Spameri\Elastic\Entity\Property\ElasticId($id),
        );

        if ($video === null) {
            $this->error('Video not found', 404);
        }

        $this->template->video = $video;
    }
}
```

## Performance Notes

- Direct ID lookup is O(1) in ElasticSearch
- No query parsing or scoring overhead
- Use this method when you know the exact ID
- For searching by other fields, see [Advanced Get](13_advanced_get.md)

## Next Steps

- [Match Get](09_match_get.md) - Search by text fields
- [Advanced Get](13_advanced_get.md) - Complex queries with filters
- [EntityManager Guide](17_entity_manager.md) - Full API reference
