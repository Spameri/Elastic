<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;

class Description implements \Spameri\Elastic\Entity\ValueInterface
{

	private ?string $value = null;


	public function __construct(
		string|null $value,
	)
	{
		$this->value = $value;
	}


	public function value(): string|null
	{
		return $this->value;
	}

}
