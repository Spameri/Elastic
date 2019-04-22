# Create and save entity

## Create
TODO
[Example](../tests/SpameriTests/Model/Insert.phpt#L16)
```php
$video = new \SpameriTests\Data\Entity\Video(
	new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
	new \SpameriTests\Data\Entity\Video\Identification(
		new \SpameriTests\Data\Entity\Property\ImdbId(4154796)
	)
);
```

## Save 
TODO
```php
$videoService->insert($video);
```

