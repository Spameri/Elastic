<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video;


class Identification implements \Spameri\Elastic\Entity\IEntity
{

	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $imdb;


	public function __construct(
		\SpameriTests\Data\Entity\Property\ImdbId $imdb
	)
	{
		$this->imdb = $imdb;
	}


	public function key() : string
	{
		return (string) $this->imdb->value();
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function imdb() : \SpameriTests\Data\Entity\Property\ImdbId
	{
		return $this->imdb;
	}
}
