<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;


class Season implements \Spameri\Elastic\Entity\EntityInterface
{
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	 */
	private $number;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Season\EpisodeCollection
	 */
	private $episodes;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $number
		, \SpameriTests\Elastic\Data\Entity\Video\Season\EpisodeCollection $episodes
	)
	{
		$this->number = $number;
		$this->episodes = $episodes;
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
