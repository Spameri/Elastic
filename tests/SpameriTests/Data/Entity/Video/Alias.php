<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;


class Alias
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\CountryShort
	 */
	private $country;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\Name $name
		, \SpameriTests\Elastic\Data\Entity\Property\CountryShort $country
	)
	{
		$this->name = $name;
		$this->country = $country;
	}


	public function name() : \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function country() : \SpameriTests\Elastic\Data\Entity\Property\CountryShort
	{
		return $this->country;
	}
}
