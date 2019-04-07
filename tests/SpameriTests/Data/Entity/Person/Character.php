<?php

namespace SpameriTests\Data\Entity\Person;


class Character implements \Spameri\Elastic\Entity\IEntity
{

	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $id;

	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId|null
	 */
	private $episode;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Identification
	 */
	private $identification;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Description
	 */
	private $biography;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Name
	 */
	private $alias;


	public function __construct(
		\SpameriTests\Data\Entity\Property\ImdbId $id
		, ?\SpameriTests\Data\Entity\Property\ImdbId $episode
		, \SpameriTests\Data\Entity\Property\Name $name
		, \SpameriTests\Data\Entity\Property\Name $alias
		, \SpameriTests\Data\Entity\Video\Identification $identification
		, \SpameriTests\Data\Entity\Property\Description $biography
	)
	{
		$this->id = $id;
		$this->episode = $episode;
		$this->name = $name;
		$this->identification = $identification;
		$this->biography = $biography;
		$this->alias = $alias;
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


	public function episode()
	{
		return $this->episode;
	}


	public function setEpisode($episode) : void
	{
		$this->episode = $episode;
	}


	public function name() : \SpameriTests\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function setName(\SpameriTests\Data\Entity\Property\Name $name) : void
	{
		$this->name = $name;
	}


	public function alias() : \SpameriTests\Data\Entity\Property\Name
	{
		return $this->alias;
	}


	public function setAlias(\SpameriTests\Data\Entity\Property\Name $alias)
	{
		$this->alias = $alias;
	}


	public function identification() : \SpameriTests\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function biography() : \SpameriTests\Data\Entity\Property\Description
	{
		return $this->biography;
	}
}
