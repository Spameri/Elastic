<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

class IntegerValue implements ValidationPropertyInterface
{

	/**
	 * @var int
	 */
	private $value;

	/**
	 * @var string
	 */
	private $key;


	public function __construct(
		int $value,
		string $key
	)
	{
		$this->value = $value;
		$this->key = $key;
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
