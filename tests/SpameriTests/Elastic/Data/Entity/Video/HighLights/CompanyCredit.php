<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\HighLights;

class CompanyCredit implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Text
	 */
	private $group;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCredit\CompanyCollection
	 */
	private $company;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\Text $group,
		\SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCredit\CompanyCollection $company,
	)
	{
		$this->group = $group;
		$this->company = $company;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return $this->group->value();
	}


	public function group(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->group;
	}


	public function company(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCredit\CompanyCollection
	{
		return $this->company;
	}

}
