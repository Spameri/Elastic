<?php

namespace SpameriTests\Data\Entity\Video\Technical;


class Color implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		?string $value
	)
	{
		if (\strlen($value) > 255) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value() : ?string
	{
		return $this->value;
	}
}