# Aggregations

## Description

Aggregations allow you to compute analytics over your data - counts, averages, distributions, and more. Unlike regular queries that return documents, aggregations return computed statistics.

## Basic Aggregation Example

Count videos by release year:

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->aggregation()->add(
    new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
        'videos-by-year',
        null,
        new \Spameri\ElasticQuery\Aggregation\Term(
            'year'
        )
    )
);

$result = $entityManager->aggregate(
    Video::class,
    $elasticQuery,
);

// Access aggregation buckets
$buckets = $result->aggregations()['videos-by-year']['buckets'];
foreach ($buckets as $bucket) {
    echo $bucket['key'] . ': ' . $bucket['doc_count'] . ' videos' . PHP_EOL;
}
// Output:
// 2024: 150 videos
// 2023: 320 videos
// 2022: 280 videos
```

## Using Aggregate Model Service

```php
// Inject Aggregate service via DI
$aggregateResult = $aggregate->execute($elasticQuery, 'videos');
```

## Common Aggregation Types

### Terms Aggregation

Group documents by field values:

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->aggregation()->add(
    new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
        'by-genre',
        null,
        new \Spameri\ElasticQuery\Aggregation\Term('genre', 20) // top 20 genres
    )
);
```

### Date Histogram

Group by time intervals:

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->aggregation()->add(
    new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
        'by-month',
        null,
        new \Spameri\ElasticQuery\Aggregation\DateHistogram(
            'createdAt',
            'month'
        )
    )
);
```

### Range Aggregation

Group by value ranges:

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->aggregation()->add(
    new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
        'by-rating',
        null,
        new \Spameri\ElasticQuery\Aggregation\Range(
            'rating',
            [
                ['to' => 3],           // Poor (0-3)
                ['from' => 3, 'to' => 7], // Average (3-7)
                ['from' => 7],          // Good (7+)
            ]
        )
    )
);
```

### Stats Aggregation

Get statistical metrics:

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->aggregation()->add(
    new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
        'rating-stats',
        null,
        new \Spameri\ElasticQuery\Aggregation\Stats('rating')
    )
);

// Result includes: count, min, max, avg, sum
```

## Combining with Queries

Filter documents before aggregating:

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();

// Filter to only 2024 videos
$elasticQuery->query()->addMust(
    new \Spameri\ElasticQuery\Query\Term('year', 2024)
);

// Aggregate by genre
$elasticQuery->aggregation()->add(
    new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
        'genres-2024',
        null,
        new \Spameri\ElasticQuery\Aggregation\Term('genre')
    )
);

$result = $entityManager->aggregate(Video::class, $elasticQuery);
```

## Nested Aggregations

Perform sub-aggregations within buckets:

```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();

// Group by year, then by genre within each year
$genreAgg = new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
    'by-genre',
    null,
    new \Spameri\ElasticQuery\Aggregation\Term('genre', 5)
);

$yearAgg = new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
    'by-year',
    $genreAgg, // nested aggregation
    new \Spameri\ElasticQuery\Aggregation\Term('year', 10)
);

$elasticQuery->aggregation()->add($yearAgg);
```

## Processing Results

The result is a `\Spameri\ElasticQuery\Response\ResultSearch` object:

```php
$result = $entityManager->aggregate(Video::class, $elasticQuery);

// Get aggregations
$aggregations = $result->aggregations();

// Access specific aggregation
$yearBuckets = $aggregations['by-year']['buckets'];

// Process buckets
$stats = [];
foreach ($yearBuckets as $bucket) {
    $stats[$bucket['key']] = [
        'count' => $bucket['doc_count'],
        'genres' => $bucket['by-genre']['buckets'] ?? [],
    ];
}
```

## Complete Example: Dashboard Statistics

```php
<?php declare(strict_types = 1);

namespace App\Service;

class DashboardService
{
    public function __construct(
        private readonly \Spameri\Elastic\EntityManager $entityManager,
    ) {}

    public function getVideoStatistics(): array
    {
        $elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();

        // Videos by year
        $elasticQuery->aggregation()->add(
            new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
                'by-year',
                null,
                new \Spameri\ElasticQuery\Aggregation\Term('year', 10)
            )
        );

        // Videos by genre
        $elasticQuery->aggregation()->add(
            new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
                'by-genre',
                null,
                new \Spameri\ElasticQuery\Aggregation\Term('genre', 10)
            )
        );

        // Rating statistics
        $elasticQuery->aggregation()->add(
            new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
                'rating-stats',
                null,
                new \Spameri\ElasticQuery\Aggregation\Stats('rating')
            )
        );

        $result = $this->entityManager->aggregate(
            \App\Model\Entity\Video::class,
            $elasticQuery,
        );

        return [
            'totalVideos' => $result->stats()->totalHits(),
            'byYear' => $result->aggregations()['by-year']['buckets'],
            'byGenre' => $result->aggregations()['by-genre']['buckets'],
            'ratingStats' => $result->aggregations()['rating-stats'],
        ];
    }
}
```

## Performance Considerations

- Aggregations can be expensive on large datasets
- Use filters to reduce the document set before aggregating
- Consider using `size: 0` if you only need aggregations, not documents
- For real-time dashboards, consider using ElasticSearch's caching

## Next Steps

- [Model Services](14_model_services.md) - Low-level aggregation API
- [Advanced Get](13_advanced_get.md) - Complex queries
- [ElasticQuery Documentation](https://github.com/Spameri/ElasticQuery/blob/master/doc/03-aggregation-objects.md) - Full aggregation reference
