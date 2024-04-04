<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;

readonly class StringValue implements \Spameri\Elastic\Entity\ValueInterface
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
