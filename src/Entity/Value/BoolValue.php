<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;

readonly class BoolValue implements \Spameri\Elastic\Entity\ValueInterface
{

	public function __construct(
		private bool $value,
	)
	{
	}


	public function value(): bool
	{
		return $this->value;
	}

}
