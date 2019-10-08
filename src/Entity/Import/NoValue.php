<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

class NoValue implements ValidationPropertyInterface
{

	public function key()
	{
		return NULL;
	}


	public function getValue()
	{
		return NULL;
	}

}
