<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Season;


class Episode implements \Spameri\Elastic\Entity\IEntity
{
	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $id;

	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $number;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Description
	 */
	private $description;


	public function __construct(
		\SpameriTests\Data\Entity\Property\ImdbId $id
		, \SpameriTests\Data\Entity\Property\ImdbId $number
		, \SpameriTests\Data\Entity\Property\Name $name
		, \SpameriTests\Data\Entity\Property\Description $description
	)
	{
		$this->id = $id;
		$this->number = $number;
		$this->name = $name;
		$this->description = $description;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function key() : string
	{
		return (string) $this->id->value();
	}


	public function id() : \SpameriTests\Data\Entity\Property\ImdbId
	{
		return $this->id;
	}


	public function number() : \SpameriTests\Data\Entity\Property\ImdbId
	{
		return $this->number;
	}


	public function name() : \SpameriTests\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function description() : \SpameriTests\Data\Entity\Property\Description
	{
		return $this->description;
	}
}
