<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class PersonFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	/**
	 * @return \Generator<\SpameriTests\Elastic\Data\Entity\Person>
	 */
	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string|null $class = null,
	): \Generator
	{
		yield new \SpameriTests\Elastic\Data\Entity\Person(
			id: new \Spameri\Elastic\Entity\Property\ElasticId($hit->id()),
			identification: new \SpameriTests\Elastic\Data\Entity\Video\Identification(
				$hit->getValue('identification.imdb'),
			),
			name: new \SpameriTests\Elastic\Data\Entity\Property\Name($hit->getValue('name')),
			description: new \SpameriTests\Elastic\Data\Entity\Property\Description($hit->getValue('description')),
			birth: new \Spameri\Elastic\Entity\Property\Date($hit->getValue('birth')),
			death: new \Spameri\Elastic\Entity\Property\Date($hit->getValue('death')),
			alias: new \SpameriTests\Elastic\Data\Entity\Property\Name($hit->getValue('alias')),
			characters: new \SpameriTests\Elastic\Data\Entity\Person\CharacterCollectionElastic(),
			jobs: new \SpameriTests\Elastic\Data\Entity\Person\JobCollectionElastic(),
		);
	}

}
