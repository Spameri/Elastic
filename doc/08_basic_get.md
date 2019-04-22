# Basic Get

## Description
Basic get by id from ElasticSearch, with exact match.

## Example
```php
$video = $videoService->get(
	new \Spameri\Elastic\Entity\Property\ElasticId($id)
);
```
