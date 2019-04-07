<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Property;


class CountryShort implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		?string $value
	)
	{
		if ($value === '' || $value === NULL) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value() : string
	{
		return $this->value;
	}
}
