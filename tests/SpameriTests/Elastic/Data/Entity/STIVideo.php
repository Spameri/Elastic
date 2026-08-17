<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Video child entity for STI testing.
 * Extends STIContent with video-specific properties.
 */
class STIVideo extends STIContent
{

	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		string $title,
		string $description,
		public int $duration,
		public string $format,
	)
	{
		parent::__construct($id, $title, $description);
	}

}
