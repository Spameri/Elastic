<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Season;

class Episode implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\ImdbId $id,
		private \SpameriTests\Elastic\Data\Entity\Property\ImdbId $number,
		private \SpameriTests\Elastic\Data\Entity\Property\Name $name,
		private \SpameriTests\Elastic\Data\Entity\Property\Description $description,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) $this->id->value();
	}


	public function id(): \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	{
		return $this->id;
	}


	public function number(): \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	{
		return $this->number;
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function description(): \SpameriTests\Elastic\Data\Entity\Property\Description
	{
		return $this->description;
	}

}
