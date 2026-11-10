# Filter data

## Description
You can specify complicated ElasticSearch Query and still get pretty entity. [EntityManager](17_entity_manager.md) accepts
`\Spameri\ElasticQuery\ElasticQuery` object and returns entity or collection depending if you want one or more results.

## Example
In this example we are looking for video named 'Avengers' made in years from 2017 to 2018.
```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->query()->must()->add(
	new \Spameri\ElasticQuery\Query\Range(
		'year',
		2017,
		2019
	)
);
$elasticQuery->query()->must()->add(
	new \Spameri\ElasticQuery\Query\ElasticMatch(
		'name',
		'Avengers'
	)
);

$video = $entityManager->findOneBy($elasticQuery, Video::class);
```
