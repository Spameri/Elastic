<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

class Person extends \Spameri\Elastic\Entity\AbstractElasticEntity
{


	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public \SpameriTests\Elastic\Data\Entity\Video\Identification $identification,
		public \SpameriTests\Elastic\Data\Entity\Property\Name $name,
		public \SpameriTests\Elastic\Data\Entity\Property\Description $description,
		public \Spameri\Elastic\Entity\Property\Date|null $birth,
		public \Spameri\Elastic\Entity\Property\Date|null $death,
		public \SpameriTests\Elastic\Data\Entity\Property\Name $alias,

		#[\Spameri\Elastic\Mapping\Collection]
		public \Spameri\Elastic\Entity\Collection\AbstractEntityCollection $characters,

		#[\Spameri\Elastic\Mapping\Collection]
		public \Spameri\Elastic\Entity\Collection\AbstractEntityCollection $jobs,
	)
	{
		parent::__construct($id);
	}

}
