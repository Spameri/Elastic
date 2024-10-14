<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;

class Url implements \Spameri\Elastic\Entity\ValueInterface
{

	public function __construct(
		private string $value,
	)
	{
		if ( ! \Nette\Utils\Validators::isUrl($value)) {
			throw new \InvalidArgumentException();
		}

	}


	public function value(): string
	{
		return $this->value;
	}

}
