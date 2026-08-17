<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

class Title extends \Spameri\Elastic\Entity\AbstractElasticEntity
{


	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public Image|null $backdrop,
	)
	{
		parent::__construct($id);
	}

}
