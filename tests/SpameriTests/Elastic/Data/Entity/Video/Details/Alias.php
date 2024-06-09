<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Details;

class Alias implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\CountryShort $country,
		private \SpameriTests\Elastic\Data\Entity\Property\Text $name,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return $this->country->value();
	}


	public function country(): \SpameriTests\Elastic\Data\Entity\Property\CountryShort
	{
		return $this->country;
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Elastic\Data\Entity\Property\Text $name): void
	{
		$this->name = $name;
	}

}
