<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Season implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\ImdbId $number,
		private \SpameriTests\Elastic\Data\Entity\Video\Season\EpisodeCollection $episodes,
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


	public function episodes(): \SpameriTests\Elastic\Data\Entity\Video\Season\EpisodeCollection
	{
		return $this->episodes;
	}

}
