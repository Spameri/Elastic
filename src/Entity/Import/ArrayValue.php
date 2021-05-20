<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

class ArrayValue implements ValidationPropertyInterface
{

	/**
	 * @var array<mixed>
	 */
	private $array;

	/**
	 * @var string
	 */
	private $key;


	/**
	 * @param array<mixed> $array
	 */
	public function __construct(
		array $array,
		string $key
	)
	{
		$this->array = $array;
		$this->key = $key;
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
