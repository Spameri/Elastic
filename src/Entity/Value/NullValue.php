<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;


class NullValue implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var NULL
	 */
	private $value;


	public function __construct()
	{
	}


	/**
	 * @return NULL
	 */
	public function value()
	{
		return $this->value;
	}

}
