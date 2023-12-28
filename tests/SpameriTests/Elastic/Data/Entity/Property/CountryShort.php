<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;

class CountryShort implements \Spameri\Elastic\Entity\ValueInterface
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string|null $value,
	)
	{
		if ($value === '' || $value === NULL) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value(): string
	{
		return $this->value;
	}

}
