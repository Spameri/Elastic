<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Property;


class Name implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $value
	)
	{
		if (\strlen($value) < 0) {
			throw new \InvalidArgumentException();
		}
		if (\strlen($value) > 255) {
			$value = \substr($value, 0, 255);
		}

		$this->value = $value;
	}


	public function value() : string
	{
		return $this->value;
	}


	public function __toString() : string
	{
		return $this->value;
	}

}
