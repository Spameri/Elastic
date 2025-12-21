<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity with nested entities and collections for testing recursive event dispatch.
 */
class EntityWithNestedContent extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	/**
	 * @param \Spameri\Elastic\Entity\Collection\EntityCollection<SimpleNestedEntity>|null $items
	 */
	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public SimpleNestedEntity|null $nested = null,
		public \Spameri\Elastic\Entity\Collection\EntityCollection|null $items = null,
	)
	{
		parent::__construct($id);
	}

}
