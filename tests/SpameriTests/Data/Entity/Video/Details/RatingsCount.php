<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Details;


class RatingsCount implements \Spameri\Elastic\Entity\ValueInterface
{
	/**
	 * @var int
	 */
	private $value;


	public function __construct(
		?int $value
	)
	{
		if ($value === NULL) {
			$value = 0;
		}

		if ($value < 0) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value() : int
	{
		return $this->value;
	}
}
