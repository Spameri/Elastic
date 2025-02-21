<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Season implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Season\EpisodeCollection<\SpameriTests\Elastic\Data\Entity\Video\Season\Episode> $episodes
	 */
	public function __construct(
		public \SpameriTests\Elastic\Data\Entity\Property\ImdbId $number,
		public \SpameriTests\Elastic\Data\Entity\Video\Season\EpisodeCollection $episodes,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) $this->number->value();
	}


	public function number(): \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	{
		return $this->number;
	}

}
