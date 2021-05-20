<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

class NoValue implements ValidationPropertyInterface
{

	/**
	 * @return NULL
	 */
	public function key()
	{
		return NULL;
	}


	/**
	 * @return NULL
	 */
	public function getValue()
	{
		return NULL;
	}

}
