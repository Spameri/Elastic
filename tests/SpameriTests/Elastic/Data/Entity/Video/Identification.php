<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Identification implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	 * @type(\SpameriTests\Elastic\Data\Entity\Video\keyword)
	 * @analyzer(\SpameriTests\Elastic\Data\Entity\Video\keyword)
	 */
	private $imdb;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $imdb,
	)
	{
		$this->imdb = $imdb;
	}


	public function key(): string
	{
		return (string) $this->imdb->value();
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function imdb(): \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	{
		return $this->imdb;
	}

}
