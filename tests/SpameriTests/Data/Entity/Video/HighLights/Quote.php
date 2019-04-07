<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\HighLights;


class Quote implements \Spameri\Elastic\Entity\IEntity
{

	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId
	 */
	private $id;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Text
	 */
	private $text;

	/**
	 * @var \SpameriTests\Data\Entity\Video\HighLights\Relevancy
	 */
	private $relevancy;


	public function __construct(
		\SpameriTests\Data\Entity\Property\ImdbId $id
		, \SpameriTests\Data\Entity\Property\Text $text
		, Relevancy $relevancy
	)
	{
		$this->id = $id;
		$this->text = $text;
		$this->relevancy = $relevancy;
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


	public function text() : \SpameriTests\Data\Entity\Property\Text
	{
		return $this->text;
	}


	public function relevancy() : Relevancy
	{
		return $this->relevancy;
	}
}
