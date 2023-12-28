<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class VideoCollectionFactory implements \Spameri\Elastic\Factory\CollectionFactoryInterface
{

	/**
	 * @param array<string> $elasticIds
	 * @return \SpameriTests\Elastic\Data\Model\VideoCollection<\SpameriTests\Elastic\Data\Entity\Video>
	 */
	public function create(
		\Spameri\Elastic\Model\ServiceInterface $service,
		array $elasticIds = [],
		\Spameri\Elastic\Entity\ElasticEntityInterface ...$entityCollection,
	): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		return new \SpameriTests\Elastic\Data\Model\VideoCollection(
			$service,
			$elasticIds,
			... $entityCollection,
		);
	}

}
