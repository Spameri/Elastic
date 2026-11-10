<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Connections;

class Spoofed implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\ImdbId $id,
		private \SpameriTests\Elastic\Data\Entity\Property\Text $note,
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


	public function note(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->note;
	}

}
