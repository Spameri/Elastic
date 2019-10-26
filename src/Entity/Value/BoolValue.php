<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;


class BoolValue implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var bool
	 */
	private $value;


	public function __construct(
		bool $value
	)
	{
		$this->value = $value;
	}


	public function value(): bool
	{
		return $this->value;
	}

}
