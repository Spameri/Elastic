<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity with nested object for testing nested hydration.
 */
class EntityWithNested extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public string $name,
		#[\Spameri\Elastic\Mapping\Entity(class: NestedObject::class)]
		public NestedObject|null $nested = null,
	)
	{
		parent::__construct($id);
	}

}
