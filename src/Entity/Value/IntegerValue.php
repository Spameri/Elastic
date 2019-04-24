<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;


class IntegerValue implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var int
	 */
	private $value;


	public function __construct(
		int $value
	)
	{
		$this->value = $value;
	}


	public function value(): int
	{
		return $this->value;
	}

}
