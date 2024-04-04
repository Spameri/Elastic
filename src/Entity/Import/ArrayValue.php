<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class ArrayValue implements ValidationPropertyInterface
{

	/**
	 * @param array<mixed> $array
	 */
	public function __construct(
		private array $array,
		private string $key,
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	/**
	 * @return array<mixed>
	 */
	public function getValue(): array
	{
		return $this->array;
	}

}
