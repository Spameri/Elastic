<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Value;

readonly class NullValue implements \Spameri\Elastic\Entity\ValueInterface
{

	public function __construct()
	{
	}


	public function value(): null
	{
		return NULL;
	}

}
