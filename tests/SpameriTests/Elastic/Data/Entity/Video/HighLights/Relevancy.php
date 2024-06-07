<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\HighLights;

class Relevancy implements \Spameri\Elastic\Entity\ValueInterface
{

	public function __construct(
		private int $value,
	)
	{
		if ($value < 0) {
			throw new \InvalidArgumentException();
		}

	}


	public function value(): int
	{
		return $this->value;
	}

}
