<?php

namespace SpameriTests\Data\Entity\Video\HighLights\CompanyCredit;


class Company implements \Spameri\Elastic\Entity\IEntity
{


	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $id;
	/**
	 * @var \SpameriTests\Data\Entity\Property\Text
	 */
	private $name;
	/**
	 * @var \SpameriTests\Data\Entity\Property\Text
	 */
	private $note;


	public function __construct(
		\SpameriTests\Data\Entity\Property\ImdbId $id
		, \SpameriTests\Data\Entity\Property\Text $name
		, \SpameriTests\Data\Entity\Property\Text $note
	)
	{
		$this->id = $id;
		$this->name = $name;
		$this->note = $note;
	}


	public function entityVariables() : array
	{
		return get_object_vars($this);
	}


	public function key() : string
	{
		return (string) $this->id->value();
	}


	public function id() : \SpameriTests\Data\Entity\Property\ImdbId
	{
		return $this->id;
	}


	public function name() : \SpameriTests\Data\Entity\Property\Text
	{
		return $this->name;
	}


	public function note() : \SpameriTests\Data\Entity\Property\Text
	{
		return $this->note;
	}
}
