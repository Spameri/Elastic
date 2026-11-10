<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\HighLights;

class AlternateVersion implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\Text $text,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return $this->text->value();
	}


	public function text(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->text;
	}

}
