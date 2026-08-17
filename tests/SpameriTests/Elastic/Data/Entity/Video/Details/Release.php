<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Details;

class Release implements \Spameri\Elastic\Entity\EntityInterface
{

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\CountryShort $country,
		private \Spameri\Elastic\Entity\Property\Date $date,
		private \SpameriTests\Elastic\Data\Entity\Property\Text $note,
	)
	{
	}


	public function key(): string
	{
		return $this->country->value();
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function country(): \SpameriTests\Elastic\Data\Entity\Property\CountryShort
	{
		return $this->country;
	}


	public function note(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->note;
	}


	public function setNote(\SpameriTests\Elastic\Data\Entity\Property\Text $note): void
	{
		$this->note = $note;
	}


	public function date(): \Spameri\Elastic\Entity\Property\Date
	{
		return $this->date;
	}


	public function setDate(\Spameri\Elastic\Entity\Property\Date $date): void
	{
		$this->date = $date;
	}

}
