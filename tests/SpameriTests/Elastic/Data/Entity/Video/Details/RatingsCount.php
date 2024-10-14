<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Details;

class RatingsCount implements \Spameri\Elastic\Entity\ValueInterface
{

	private int $value;


	public function __construct(
		int|null $value,
	)
	{
		if ($value === null) {
			$value = 0;
		}

		if ($value < 0) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value(): int
	{
		return $this->value;
	}

}
