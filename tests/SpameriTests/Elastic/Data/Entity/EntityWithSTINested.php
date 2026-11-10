<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity with STI nested object for testing #[STIEntity] attribute.
 */
class EntityWithSTINested extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public string $name,
		#[\Spameri\Elastic\Mapping\STIEntity]
		public STIMediaInfo $mediaInfo,
	)
	{
		parent::__construct($id);
	}

}
