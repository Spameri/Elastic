<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Person;

class Character implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	 */
	private $id;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\ImdbId|null
	 */
	private $episode;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Identification
	 */
	private $identification;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Description
	 */
	private $biography;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $alias;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $id,
		?\SpameriTests\Elastic\Data\Entity\Property\ImdbId $episode,
		\SpameriTests\Elastic\Data\Entity\Property\Name $name,
		\SpameriTests\Elastic\Data\Entity\Property\Name $alias,
		\SpameriTests\Elastic\Data\Entity\Video\Identification $identification,
		\SpameriTests\Elastic\Data\Entity\Property\Description $biography
	)
	{
		$this->id = $id;
		$this->episode = $episode;
		$this->name = $name;
		$this->identification = $identification;
		$this->biography = $biography;
		$this->alias = $alias;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) $this->id->value();
	}


	public function id(): \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	{
		return $this->id;
	}


	public function episode(): ?\SpameriTests\Elastic\Data\Entity\Property\ImdbId
	{
		return $this->episode;
	}


	public function setEpisode(?\SpameriTests\Elastic\Data\Entity\Property\ImdbId $episode): void
	{
		$this->episode = $episode;
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function setName(\SpameriTests\Elastic\Data\Entity\Property\Name $name): void
	{
		$this->name = $name;
	}


	public function alias(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->alias;
	}


	public function setAlias(\SpameriTests\Elastic\Data\Entity\Property\Name $alias): void
	{
		$this->alias = $alias;
	}


	public function identification(): \SpameriTests\Elastic\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function biography(): \SpameriTests\Elastic\Data\Entity\Property\Description
	{
		return $this->biography;
	}

}
