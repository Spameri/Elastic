<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class NoValue implements ValidationPropertyInterface
{

	public function key(): null
	{
		return null;
	}


	public function getValue(): null
	{
		return null;
	}

}
