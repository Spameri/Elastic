<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Connections;

class EditedFrom implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\ImdbId
	 */
	private $id;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Text
	 */
	private $note;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\ImdbId $id,
		\SpameriTests\Elastic\Data\Entity\Property\Text $note,
	)
	{
		$this->id = $id;
		$this->note = $note;
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


	public function note(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->note;
	}

}
