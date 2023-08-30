<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;

interface CollectionFactoryInterface
{

	/**
	 * @param array<string> $elasticIds
	 * @return \Spameri\Elastic\Entity\ElasticEntityCollectionInterface<\Spameri\Elastic\Entity\ElasticEntityInterface>
	 */
	public function create(
		\Spameri\Elastic\Model\ServiceInterface $service,
		array $elasticIds = [],
		\Spameri\Elastic\Entity\ElasticEntityInterface ...$entityCollection
	): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface;

}
