<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;

class ImdbId implements \Spameri\Elastic\Entity\ValueInterface
{

	/**
	 * @var int
	 */
	private $value;


	public function __construct(
		int $value,
	)
	{
		if ($value < 1) {
			throw new \InvalidArgumentException();
		}
		if ($value > 99999999) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value(): int
	{
		return $this->value;
	}

}
