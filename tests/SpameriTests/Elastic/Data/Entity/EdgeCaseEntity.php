<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity for edge case testing (special characters, large content, etc.).
 */
class EdgeCaseEntity extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public string $name,
		public string $content,
		public string|null $description = null,
	)
	{
		parent::__construct($id);
	}

}
