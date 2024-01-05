<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

class Person implements \Spameri\Elastic\Entity\ElasticEntityInterface
{


	public function __construct(
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		private \SpameriTests\Elastic\Data\Entity\Video\Identification $identification,
		private \SpameriTests\Elastic\Data\Entity\Property\Name $name,
		private \SpameriTests\Elastic\Data\Entity\Property\Description $description,
		private \Spameri\Elastic\Entity\Property\Date|null $birth,
		private \Spameri\Elastic\Entity\Property\Date|null $death,
		private \SpameriTests\Elastic\Data\Entity\Property\Name $alias,
//		private \SpameriTests\Elastic\Data\Entity\Person\CharacterCollectionElastic $characters,
//		private \SpameriTests\Elastic\Data\Entity\Person\JobCollectionElastic $jobs,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function id(): \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->id;
	}


	public function identification(): \SpameriTests\Elastic\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Elastic\Data\Entity\Property\Name $name): void
	{
		$this->name = $name;
	}


	public function description(): \SpameriTests\Elastic\Data\Entity\Property\Description
	{
		return $this->description;
	}


	public function changeDescription(\SpameriTests\Elastic\Data\Entity\Property\Description $description): void
	{
		$this->description = $description;
	}


	public function birth(): \Spameri\Elastic\Entity\Property\Date|null
	{
		return $this->birth;
	}


	public function setBirth(\Spameri\Elastic\Entity\Property\Date|null $birth): void
	{
		$this->birth = $birth;
	}


	public function death(): \Spameri\Elastic\Entity\Property\Date|null
	{
		return $this->death;
	}


	public function setDeath(\Spameri\Elastic\Entity\Property\Date|null $death): void
	{
		$this->death = $death;
	}


	public function alias(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->alias;
	}


	public function setAlias(\SpameriTests\Elastic\Data\Entity\Property\Name $alias): void
	{
		$this->alias = $alias;
	}


	public function characters(): \SpameriTests\Elastic\Data\Entity\Person\CharacterCollectionElastic
	{
		return $this->characters;
	}


	public function jobs(): \SpameriTests\Elastic\Data\Entity\Person\JobCollectionElastic
	{
		return $this->jobs;
	}

}
