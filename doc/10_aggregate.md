# Aggregate

## Description
TODO

## Example
```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->aggregation()->add(
	new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
		'video-by-year',
		NULL,
		new \Spameri\ElasticQuery\Aggregation\Term(
			'year'
		)
	)
);

$aggregateResult = $videoService->aggregate($elasticQuery);
```
