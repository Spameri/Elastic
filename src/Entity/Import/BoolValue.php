<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class BoolValue implements ValidationPropertyInterface
{

	public function __construct(
		private bool $value,
		private string $key,
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	public function getValue(): bool
	{
		return $this->value;
	}

}
