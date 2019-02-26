<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


class EntityClassResolver
{

	/**
	 * @var \Spameri\Elastic\Model\EntityMappingProvider
	 */
	private $entityMappingProvider;


	public function __construct(
		\Spameri\Elastic\Model\EntityMappingProvider $entityMappingProvider
	)
	{
		$this->entityMappingProvider = $entityMappingProvider;
	}


	public function getEntityClass(
		\Spameri\ElasticQuery\Response\Result\Hit $hit
	) : string
	{
		foreach ($this->entityMappingProvider->provide() as $entity) {
			if (\strpos($hit->index(), $entity['index']) === 0) {
				return $entity['class'];
			}
		}

		throw new \Spameri\Elastic\Exception\EntityClassNotResolved($hit->index());
	}

}
