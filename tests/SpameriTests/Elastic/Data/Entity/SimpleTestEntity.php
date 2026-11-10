<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Simple entity with scalar types for basic hydration testing.
 */
class SimpleTestEntity extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public string $name,
		public int $count,
		public float $price,
		public bool $active,
		public string|null $description = null,
	)
	{
		parent::__construct($id);
	}

}
