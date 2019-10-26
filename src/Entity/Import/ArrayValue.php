<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

class ArrayValue implements ValidationPropertyInterface
{

	/**
	 * @var array
	 */
	private $array;

	/**
	 * @var string
	 */
	private $key;


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


	public function getValue(): array
	{
		return $this->array;
	}

}
