<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Abstract parent entity for STI testing.
 * Implements STIElasticEntityInterface to enable Single Table Inheritance.
 */
abstract class STIContent extends \Spameri\Elastic\Entity\AbstractElasticEntity
	implements \Spameri\Elastic\Entity\STIElasticEntityInterface
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public string $title,
		public string $description,
	)
	{
		parent::__construct($id);
	}

}
