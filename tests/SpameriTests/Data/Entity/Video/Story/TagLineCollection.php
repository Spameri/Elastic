<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Story;


class TagLineCollection implements \Spameri\Elastic\Entity\ValueCollectionInterface
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


	/**
	 * @return \ArrayIterator<\SpameriTests\Data\Entity\Video\Story\TagLine>
	 */
	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}
