<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Story;

class KeyWord implements \Spameri\Elastic\Entity\ValueInterface
{

	public function __construct(
		private string $value,
	)
	{
		if ($value === '') {
			throw new \InvalidArgumentException();
		}
		if (\strlen($value) > 55) {
			throw new \InvalidArgumentException();
		}

	}


	public function value(): string
	{
		return $this->value;
	}

}
