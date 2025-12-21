<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Entity with value objects for testing value object hydration.
 */
class EntityWithValueObjects extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public \Spameri\Elastic\Entity\Value\StringValue $stringValue,
		public \Spameri\Elastic\Entity\Value\IntegerValue $intValue,
		public \Spameri\Elastic\Entity\Value\BoolValue $boolValue,
		public \Spameri\Elastic\Entity\Property\Date|null $dateValue = null,
		public \Spameri\Elastic\Entity\Property\DateTime|null $dateTimeValue = null,
	)
	{
		parent::__construct($id);
	}

}
