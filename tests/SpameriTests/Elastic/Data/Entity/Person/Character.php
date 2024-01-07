<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Person;

class Character implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		public string $id,
		public \SpameriTests\Elastic\Data\Entity\Property\ImdbId $episode,
		public \SpameriTests\Elastic\Data\Entity\Property\Name $name,
		public \SpameriTests\Elastic\Data\Entity\Property\Name $alias,
		public \SpameriTests\Elastic\Data\Entity\Video\Identification $identification,
		public \SpameriTests\Elastic\Data\Entity\Property\Description $biography,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return $this->id;
	}

}
