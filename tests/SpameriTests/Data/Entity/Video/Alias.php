<?php

namespace SpameriTests\Data\Entity\Video;


class Alias
{

	/**
	 * @var \SpameriTests\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Data\Entity\Property\CountryShort
	 */
	private $country;


	public function __construct(
		\SpameriTests\Data\Entity\Property\Name $name
		, \SpameriTests\Data\Entity\Property\CountryShort $country
	)
	{
		$this->name = $name;
		$this->country = $country;
	}


	public function name() : \SpameriTests\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function country() : \SpameriTests\Data\Entity\Property\CountryShort
	{
		return $this->country;
	}
}