<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Story;

class TagLine implements \Spameri\Elastic\Entity\ValueInterface
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


	public function value(): string
	{
		return $this->value;
	}

}
