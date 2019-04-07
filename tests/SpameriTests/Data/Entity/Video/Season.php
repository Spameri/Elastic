<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video;


class Season implements \Spameri\Elastic\Entity\IEntity
{
	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $number;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Season\EpisodeCollection
	 */
	private $episodes;


	public function __construct(
		\SpameriTests\Data\Entity\Property\ImdbId $number
		, \SpameriTests\Data\Entity\Video\Season\EpisodeCollection $episodes
	)
	{
		$this->number = $number;
		$this->episodes = $episodes;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function key() : string
	{
		return (string) $this->number->value();
	}
	
	
	public function number() : \SpameriTests\Data\Entity\Property\ImdbId
	{
		return $this->number;
	}


	public function episodes() : \SpameriTests\Data\Entity\Video\Season\EpisodeCollection
	{
		return $this->episodes;
	}
}
