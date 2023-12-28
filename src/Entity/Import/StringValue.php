<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

class StringValue implements ValidationPropertyInterface
{

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @var string
	 */
	private $key;


	public function __construct(
		string $value,
		string $key,
	)
	{
		$this->value = $value;
		$this->key = $key;
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
