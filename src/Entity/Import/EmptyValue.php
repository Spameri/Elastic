<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;


class EmptyValue implements ValidationPropertyInterface
{

	/**
	 * @var string
	 */
	private $key;


	public function __construct(
		string $key
	)
	{
		$this->key = $key;
	}


	public function key(): string
	{
		return $this->key;
	}


	public function getValue()
	{
		return NULL;
	}

}
