<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Story;


/**
 * @template-covariant T of \SpameriTests\Elastic\Data\Entity\Video\Story\TagLine
 * @template-implements \Spameri\Elastic\Entity\ValueCollectionInterface<T>
 */
class TagLineCollection implements \Spameri\Elastic\Entity\ValueCollectionInterface
{

	/**
	 * @var array<\SpameriTests\Elastic\Data\Entity\Video\Story\TagLine>
	 */
	private array $collection;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Video\Story\TagLine ...$collection,
	)
	{
		$this->collection = [];
		foreach ($collection as $tagLine) {
			$this->add($tagLine);
		}
	}


	public function add(
		\SpameriTests\Elastic\Data\Entity\Video\Story\TagLine $tagLine,
	): void
	{
		$this->collection[\md5($tagLine->value())] = $tagLine;
	}


	/**
	 * @return \ArrayIterator<int|string, \SpameriTests\Elastic\Data\Entity\Video\Story\TagLine>
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}

}
