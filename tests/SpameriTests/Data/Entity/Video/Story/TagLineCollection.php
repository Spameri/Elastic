<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Story;


class TagLineCollection implements \Spameri\Elastic\Entity\IValueCollection
{

	/**
	 * @var array<\SpameriTests\Data\Entity\Video\Story\TagLine>
	 */
	private $collection;


	public function __construct(
		\SpameriTests\Data\Entity\Video\Story\TagLine ... $collection
	)
	{
		$this->collection = [];
		foreach ($collection as $tagLine) {
			$this->add($tagLine);
		}
	}


	public function add(
		\SpameriTests\Data\Entity\Video\Story\TagLine $tagLine
	) : void
	{
		$this->collection[\md5($tagLine->value())] = $tagLine;
	}


	public function getIterator()
	{
		return new \ArrayIterator($this->collection);
	}
}
