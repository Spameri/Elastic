<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

interface ValidationPropertyInterface
{

	/**
	 * @return mixed
	 */
	public function key();

	/**
	 * @return mixed
	 */
	public function getValue();

}
