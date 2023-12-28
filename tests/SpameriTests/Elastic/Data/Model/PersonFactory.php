<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class PersonFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	/**
	 * @return \Generator<\SpameriTests\Elastic\Data\Entity\Person>
	 */
	public function create(\Spameri\ElasticQuery\Response\Result\Hit $hit): \Generator
	{
		yield new \SpameriTests\Elastic\Data\Entity\Person(
			new \Spameri\Elastic\Entity\Property\ElasticId($hit->id()),
			new \SpameriTests\Elastic\Data\Entity\Video\Identification(
				$hit->getValue('identification.imdb'),
			),
			new \SpameriTests\Elastic\Data\Entity\Property\Name($hit->getValue('name')),
			new \SpameriTests\Elastic\Data\Entity\Property\Description($hit->getValue('description')),
			new \Spameri\Elastic\Entity\Property\Date($hit->getValue('birth')),
			new \Spameri\Elastic\Entity\Property\Date($hit->getValue('death')),
			new \SpameriTests\Elastic\Data\Entity\Property\Name($hit->getValue('alias')),
			new \SpameriTests\Elastic\Data\Entity\Person\CharacterCollectionElastic(),
			new \SpameriTests\Elastic\Data\Entity\Person\JobCollectionElastic(),
		);
	}

}
