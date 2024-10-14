<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;

interface CollectionFactoryInterface
{

	/**
	 * @param class-string $entityClass
	 * @param array<string> $elasticIds
	 * @return \Spameri\Elastic\Entity\ElasticEntityCollectionInterface<\Spameri\Elastic\Entity\ElasticEntityInterface>
	 */
	public function create(
		\Spameri\Elastic\EntityManager $entityManager,
		string $entityClass,
		array $elasticIds = [],
		\Spameri\Elastic\Entity\ElasticEntityInterface ...$entityCollection,
	): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface;

}
