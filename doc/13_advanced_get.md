# Advanced Get

## Description
TODO
Get data from ElasticSearch by tag.

## Example
```php
$video = $videoService->getBy(
	new \Spameri\ElasticQuery\ElasticQuery(
		new \Spameri\ElasticQuery\Query\QueryCollection(
			new \Spameri\ElasticQuery\Query\MustCollection(
				new \Spameri\ElasticQuery\Query\Match(
					'story.tag',
					'action'
				)
			)
		)
	)
);
```
