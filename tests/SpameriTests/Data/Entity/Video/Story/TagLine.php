<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Story;


class TagLine implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $tagLine
	)
	{
		if ($tagLine === '') {
			throw new \InvalidArgumentException();
		}

		$this->value = $tagLine;
	}


	public function value() : string
	{
		return $this->value;
	}
}
