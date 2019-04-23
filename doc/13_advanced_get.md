# Advanced Get

## Description


## Example
In this example we want videos with tag **action** and are public. Also we want only first 50 sorted by year, but if 
year has multiple videos sort them by score. Bonus points if movie is on beach with someone named john or here 
misspelled as jon.

Query does not have to be specified all at once, this is demonstration of capabilities. Query can be constructed empty 
and extended through application runtime. 
```php
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery(
	new \Spameri\ElasticQuery\Query\QueryCollection(
		new \Spameri\ElasticQuery\Query\MustCollection(
			new \Spameri\ElasticQuery\Query\Term(
				'story.tag',
				'action'
			),
			new \Spameri\ElasticQuery\Query\Term(
				'isPublic',
				TRUE
			)
		),
		new \Spameri\ElasticQuery\Query\ShouldCollection(
			new \Spameri\ElasticQuery\Query\Match(
				'story.description',
				'beach'
			),
			new \Spameri\ElasticQuery\Query\Fuzzy(
				'story.description',
				'jon'
			)
		)
	),
	NULL,
	NULL,
	new \Spameri\ElasticQuery\Options(
		50,
		NULL,
		new \Spameri\ElasticQuery\Options\SortCollection(
			new \Spameri\ElasticQuery\Options\Sort(
				'year',
				\Spameri\ElasticQuery\Options\Sort::DESC
			),
			new \Spameri\ElasticQuery\Options\Sort(
				'_score',
				\Spameri\ElasticQuery\Options\Sort::DESC
			)
		)
	)
);
$videos = $this->videoService->getAllBy($elasticQuery);
```
