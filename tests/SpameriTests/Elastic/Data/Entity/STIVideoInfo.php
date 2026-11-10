<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Video info child entity for nested STI testing.
 */
class STIVideoInfo extends STIMediaInfo
{

	public function __construct(
		public int $width,
		public int $height,
		public string $codec,
	)
	{
	}


	public function key(): string
	{
		return 'videoInfo';
	}

}
