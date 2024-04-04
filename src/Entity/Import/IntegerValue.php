<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class IntegerValue implements ValidationPropertyInterface
{

	public function __construct(
		private int $value,
		private string $key,
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	public function getValue(): int
	{
		return $this->value;
	}

}
