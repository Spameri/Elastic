# Filter data

## Description
TODO

## Example
```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->query()->must()->add(
	new \Spameri\ElasticQuery\Query\Range(
		'year',
		2018,
		2019
	)
);
$elasticQuery->query()->must()->add(
	new \Spameri\ElasticQuery\Query\Match(
		'name',
		'Avengers'
	)
);

$video = $videoService->getBy($elasticQuery);
```
