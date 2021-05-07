<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

class BoolValue implements ValidationPropertyInterface
{

	/**
	 * @var bool
	 */
	private $value;

	/**
	 * @var string
	 */
	private $key;


	public function __construct(
		bool $value,
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


	public function getValue(): bool
	{
		return $this->value;
	}

}
