# Entity factory

## Description
TODO
Creates entity from result hit.

## Example
```php
namespace SpameriTests\Elastic\Factory;


class VideoFactory implements \Spameri\Elastic\Factory\IEntityFactory
{
	
	public function create(\Spameri\ElasticQuery\Response\Result\Hit $hit)
	{
		return new \SpameriTests\Data\Entity\Video(
			new \Spameri\Elastic\Entity\Property\ElasticId($hit->getValue('id')),
			new \SpameriTests\Data\Entity\Video\Identification(
				new \SpameriTests\Data\Entity\Property\ImdbId($hit->getValue('identification.imdb'))
			)
		);
	}
	
}
```
