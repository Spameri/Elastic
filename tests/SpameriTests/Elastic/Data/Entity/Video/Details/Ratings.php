<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Details;

class Ratings implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Details\RatingsCount
	 */
	private $imdbRatings;


	public function __construct(
		RatingsCount $imdbRatings
	)
	{
		$this->imdbRatings = $imdbRatings;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) \spl_object_id($this);
	}


	public function imdbRatings(): \SpameriTests\Elastic\Data\Entity\Video\Details\RatingsCount
	{
		return $this->imdbRatings;
	}


	public function setImdbRatings(\SpameriTests\Elastic\Data\Entity\Video\Details\RatingsCount $imdbRatings): void
	{
		$this->imdbRatings = $imdbRatings;
	}

}
