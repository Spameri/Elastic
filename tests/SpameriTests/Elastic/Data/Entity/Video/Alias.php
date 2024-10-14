<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Alias
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\Name $name,
		private \SpameriTests\Elastic\Data\Entity\Property\CountryShort $country,
	)
	{
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function country(): \SpameriTests\Elastic\Data\Entity\Property\CountryShort
	{
		return $this->country;
	}

}
