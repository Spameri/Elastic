<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Details;


class Ratings implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Data\Entity\Video\Details\RatingsCount
	 */
	private $imdbRatings;


	public function __construct(
		RatingsCount $imdbRatings
	)
	{
		$this->imdbRatings = $imdbRatings;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function key() : string
	{

	}


	public function imdbRatings() : \SpameriTests\Data\Entity\Video\Details\RatingsCount
	{
		return $this->imdbRatings;
	}


	public function setImdbRatings(\SpameriTests\Data\Entity\Video\Details\RatingsCount $imdbRatings)
	{
		$this->imdbRatings = $imdbRatings;
	}

}
