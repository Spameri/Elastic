<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class FloatValue implements ValidationPropertyInterface
{

	public function __construct(
		private float $value,
		private string $key,
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	public function getValue(): float
	{
		return $this->value;
	}

}
