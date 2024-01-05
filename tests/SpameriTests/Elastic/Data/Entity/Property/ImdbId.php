<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;

class ImdbId implements \Spameri\Elastic\Entity\ValueInterface
{

	public function __construct(
		private string $value,
	)
	{
	}


	public function value(): string
	{
		return $this->value;
	}

}
