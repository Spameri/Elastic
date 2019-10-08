<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;


class FloatValue implements ValidationPropertyInterface
{

	/**
	 * @var float
	 */
	private $value;

	/**
	 * @var string
	 */
	private $key;


	public function __construct(
		float $value,
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


	public function getValue(): float
	{
		return $this->value;
	}

}
