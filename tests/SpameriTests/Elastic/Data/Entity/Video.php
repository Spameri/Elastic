<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

class Video extends \Spameri\Elastic\Entity\AbstractElasticEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \Spameri\Elastic\Entity\Property\ElasticId::class)]
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		public \SpameriTests\Elastic\Data\Entity\Video\Identification $identification,
		public \SpameriTests\Elastic\Data\Entity\Property\Name $name,
		public \SpameriTests\Elastic\Data\Entity\Property\Year $year,
		public \SpameriTests\Elastic\Data\Entity\Video\Technical $technical,
		public \SpameriTests\Elastic\Data\Entity\Video\Story $story,
		public \SpameriTests\Elastic\Data\Entity\Video\Details $details,
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights $highLights,
		public \SpameriTests\Elastic\Data\Entity\Video\Connections $connections,
		public \SpameriTests\Elastic\Data\Entity\Video\People $people,

		#[\Spameri\Elastic\Mapping\Collection]
		public \Spameri\Elastic\Entity\Collection\AbstractEntityCollection $season,
	)
	{
		parent::__construct($id);
	}

}
