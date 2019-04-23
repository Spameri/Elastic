# Create and save entity

## Create
- When creating new entity, mostly you need to construct it manually.
- Your data is from another database or API or csv or wherever. Quality may wary.
- So best approach is to validate all you can with objects.
- In this example we have property **imdb** and it needs to be in range from one digit to ten digits, rather than adding 
if conditions wherever we create entity we use **ImdbId** class to validate this rule and this helps to properly convert 
entity data to array.
- [Example](../tests/SpameriTests/Model/Insert.phpt#L16)
```php
$sqlData = $dibi->fetchRow();
$video = new \SpameriTests\Data\Entity\Video(
	new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
	new \SpameriTests\Data\Entity\Video\Identification(
		new \SpameriTests\Data\Entity\Property\ImdbId($sqlData['imdb'])
	)
);
```

## Save 
- Entity is created, validated and ready to be saved. Just pass entity to [service](12_entity_service.md). And done.
```php
$videoService->insert($video);
```
