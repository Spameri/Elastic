<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity with a collection of nested objects for testing collection hydration.
 */
class EntityWithCollection extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	/**
	 * @param \Spameri\Elastic\Entity\Collection\EntityCollection<\SpameriTests\Elastic\Data\Entity\NestedObject> $items
	 */
	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public string $name,
		#[\Spameri\Elastic\Mapping\Collection]
		public \Spameri\Elastic\Entity\Collection\EntityCollection $items,
	)
	{
		parent::__construct($id);
	}

}
