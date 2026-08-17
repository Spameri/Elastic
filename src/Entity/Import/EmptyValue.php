<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class EmptyValue implements ValidationPropertyInterface
{

	public function __construct(
		private string $key,
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	public function getValue(): null
	{
		return null;
	}

}
