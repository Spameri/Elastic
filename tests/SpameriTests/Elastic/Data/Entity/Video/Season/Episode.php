<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Season;


class Episode implements \Spameri\Elastic\Entity\EntityInterface
{
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	 */
	private $id;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	 */
	private $number;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Description
	 */
	private $description;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $id
		, \SpameriTests\Elastic\Data\Entity\Property\ImdbId $number
		, \SpameriTests\Elastic\Data\Entity\Property\Name $name
		, \SpameriTests\Elastic\Data\Entity\Property\Description $description
	)
	{
		$this->id = $id;
		$this->number = $number;
		$this->name = $name;
		$this->description = $description;
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


	public function number(): \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	{
		return $this->number;
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function description(): \SpameriTests\Elastic\Data\Entity\Property\Description
	{
		return $this->description;
	}
}
