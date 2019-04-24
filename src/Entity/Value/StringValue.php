<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;


class StringValue implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $value
	)
	{
		$this->value = $value;
	}


	public function value(): string
	{
		return $this->value;
	}

}
