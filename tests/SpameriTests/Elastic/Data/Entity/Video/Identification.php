<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Identification implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		public \SpameriTests\Elastic\Data\Entity\Property\ImdbId $imdb,
	)
	{
	}


	public function key(): string
	{
		return (string) $this->imdb->value();
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}

}
