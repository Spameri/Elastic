<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Connections;


class Featured implements \Spameri\Elastic\Entity\EntityInterface
{
	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $id;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Text
	 */
	private $note;


	public function __construct(
		\SpameriTests\Data\Entity\Property\ImdbId $id
		, \SpameriTests\Data\Entity\Property\Text $note
	)
	{
		$this->id = $id;
		$this->note = $note;
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


	public function note() : \SpameriTests\Data\Entity\Property\Text
	{
		return $this->note;
	}
}
