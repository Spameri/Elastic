<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\HighLights;

class CrazyCredit implements \Spameri\Elastic\Entity\EntityInterface
{

	private \SpameriTests\Elastic\Data\Entity\Video\HighLights\Relevancy $relevancy;


	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\ImdbId $id,
		private \SpameriTests\Elastic\Data\Entity\Property\Text $text,
		Relevancy $relevancy,
	)
	{
		$this->relevancy = $relevancy;
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


	public function text(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->text;
	}


	public function relevancy(): Relevancy
	{
		return $this->relevancy;
	}

}
