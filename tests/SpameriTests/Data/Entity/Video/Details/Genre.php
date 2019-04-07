<?php

namespace SpameriTests\Data\Entity\Video\Details;


class Genre implements \Spameri\Elastic\Entity\IValue
{
	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $value
	)
	{
		if ($value === '') {
			throw new \InvalidArgumentException();
		}
		if (\strlen($value) > 65) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value() : string
	{
		return $this->value;
	}
}
