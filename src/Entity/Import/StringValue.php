<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class StringValue implements ValidationPropertyInterface
{

	public function __construct(
		private string $value,
		private string $key,
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	public function getValue(): string
	{
		return $this->value;
	}

}
