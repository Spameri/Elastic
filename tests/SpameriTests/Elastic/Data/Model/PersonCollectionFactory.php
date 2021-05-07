<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class PersonCollectionFactory implements \Spameri\Elastic\Factory\CollectionFactoryInterface
{

	/**
	 * @param array<string> $elasticIds
	 * @return \SpameriTests\Elastic\Data\Model\PersonCollection<\SpameriTests\Elastic\Data\Entity\Person>
	 */
	public function create(
		\Spameri\Elastic\Model\ServiceInterface $service,
		array $elasticIds = [],
		\Spameri\Elastic\Entity\ElasticEntityInterface ... $entityCollection
	): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		return new \SpameriTests\Elastic\Data\Model\PersonCollection(
			$service,
			$elasticIds,
			... $entityCollection
		);
	}

}
