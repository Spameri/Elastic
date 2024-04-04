<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;

readonly class IntegerValue implements \Spameri\Elastic\Entity\ValueInterface
{

	public function __construct(
		private int $value,
	)
	{
	}


	public function value(): int
	{
		return $this->value;
	}

}
