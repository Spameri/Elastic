<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\HighLights;

class Location implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Text
	 */
	private $name;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Text
	 */
	private $note;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\Text $name,
		\SpameriTests\Elastic\Data\Entity\Property\Text $note
	)
	{
		$this->name = $name;
		$this->note = $note;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return $this->name->value();
	}


	public function getName(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->name;
	}


	public function getNote(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->note;
	}

}
