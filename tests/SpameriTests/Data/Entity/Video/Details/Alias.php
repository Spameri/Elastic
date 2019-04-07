<?php

namespace SpameriTests\Data\Entity\Video\Details;


class Alias implements \Spameri\Elastic\Entity\IEntity
{
	/**
	 * @var \SpameriTests\Data\Entity\Property\CountryShort
	 */
	private $country;
	
	/**
	 * @var \SpameriTests\Data\Entity\Property\Text
	 */
	private $name;


	public function __construct(
		\SpameriTests\Data\Entity\Property\CountryShort $country
		, \SpameriTests\Data\Entity\Property\Text $name
	)
	{
		$this->country = $country;
		$this->name = $name;
	}


	public function entityVariables() : array
	{
		return get_object_vars($this);
	}


	public function key() : string
	{
		return $this->country->value();
	}


	public function country(): \SpameriTests\Data\Entity\Property\CountryShort
	{
		return $this->country;
	}


	public function name(): \SpameriTests\Data\Entity\Property\Text
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Data\Entity\Property\Text $name)
	{
		$this->name = $name;
	}
}
