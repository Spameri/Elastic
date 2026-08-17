<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Audio info child entity for nested STI testing.
 */
class STIAudioInfo extends STIMediaInfo
{

	public function __construct(
		public int $bitrate,
		public string $codec,
	)
	{
	}


	public function key(): string
	{
		return 'audioInfo';
	}

}
