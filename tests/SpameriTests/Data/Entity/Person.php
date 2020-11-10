<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;


class Person implements \Spameri\Elastic\Entity\ElasticEntityInterface
{

	/**
	 * @var \Spameri\Elastic\Entity\Property\ElasticIdInterface
	 */
	private $id;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Identification
	 */
	private $identification;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Description
	 */
	private $description;

	/**
	 * @var \Spameri\Elastic\Entity\Property\Date|NULL
	 */
	private $birth;

	/**
	 * @var \Spameri\Elastic\Entity\Property\Date|NULL
	 */
	private $death;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $alias;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Person\CharacterCollectionElastic
	 */
	private $characters;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Person\JobCollectionElastic
	 */
	private $jobs;


	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id
		, \SpameriTests\Elastic\Data\Entity\Video\Identification $identification
		, \SpameriTests\Elastic\Data\Entity\Property\Name $name
		, \SpameriTests\Elastic\Data\Entity\Property\Description $description
		, ?\Spameri\Elastic\Entity\Property\Date $birth
		, ?\Spameri\Elastic\Entity\Property\Date $death
		, \SpameriTests\Elastic\Data\Entity\Property\Name $alias
		, \SpameriTests\Elastic\Data\Entity\Person\CharacterCollectionElastic $characters
		, \SpameriTests\Elastic\Data\Entity\Person\JobCollectionElastic $jobs
	)
	{
		$this->id = $id;
		$this->identification = $identification;
		$this->name = $name;
		$this->description = $description;
		$this->birth = $birth;
		$this->death = $death;
		$this->alias = $alias;
		$this->characters = $characters;
		$this->jobs = $jobs;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function id() : \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->id;
	}


	public function identification() : \SpameriTests\Elastic\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function name() : \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Elastic\Data\Entity\Property\Name $name) : void
	{
		$this->name = $name;
	}


	public function description() : \SpameriTests\Elastic\Data\Entity\Property\Description
	{
		return $this->description;
	}

	public function changeDescription(\SpameriTests\Elastic\Data\Entity\Property\Description $description) : void
	{
		$this->description = $description;
	}

	public function birth() : ?\Spameri\Elastic\Entity\Property\Date
	{
		return $this->birth;
	}


	public function setBirth(?\Spameri\Elastic\Entity\Property\Date $birth) : void
	{
		$this->birth = $birth;
	}


	public function death() : ?\Spameri\Elastic\Entity\Property\Date
	{
		return $this->death;
	}


	public function setDeath(?\Spameri\Elastic\Entity\Property\Date $death) : void
	{
		$this->death = $death;
	}


	public function alias() : \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->alias;
	}


	public function setAlias(\SpameriTests\Elastic\Data\Entity\Property\Name $alias) : void
	{
		$this->alias = $alias;
	}


	public function characters() : \SpameriTests\Elastic\Data\Entity\Person\CharacterCollectionElastic
	{
		return $this->characters;
	}


	public function jobs() : \SpameriTests\Elastic\Data\Entity\Person\JobCollectionElastic
	{
		return $this->jobs;
	}

}
