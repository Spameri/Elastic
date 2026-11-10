<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity with an ElasticEntityCollection for testing lazy loading of elastic collections.
 */
class EntityWithElasticCollection extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	/**
	 * @param \Spameri\Elastic\Entity\Collection\ElasticEntityCollection<\SpameriTests\Elastic\Data\Entity\SimpleTestEntity> $related
	 */
	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public string $name,
		#[\Spameri\Elastic\Mapping\ElasticCollection(class: \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class)]
		public \Spameri\Elastic\Entity\Collection\ElasticEntityCollection $related,
	)
	{
		parent::__construct($id);
	}

}
