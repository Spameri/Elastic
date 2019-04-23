# Aggregate

## Description
When aggregating you dont get directly entity just array data, for this we have `\Spameri\ElasticQuery\Response\ResultSearch`
object to encapsulate result.

## Example
In this example we want number of videos released in each year.
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
