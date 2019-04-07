<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Property;


class Year implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var int
	 */
	private $value;


	public function __construct(
		int $year
	)
	{
		if ($year < 1800) {
			throw new \InvalidArgumentException();
		}
		if ($year > 2100) {
			throw new \InvalidArgumentException();
		}

		$this->value = $year;
	}


	public function value() : int
	{
		return $this->value;
	}


	public function __toString() : string
	{
		return (string) $this->value;
	}
}
