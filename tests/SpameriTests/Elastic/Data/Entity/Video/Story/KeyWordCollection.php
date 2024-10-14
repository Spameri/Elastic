<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Story;

class KeyWordCollection implements \Spameri\Elastic\Entity\ValueCollectionInterface
{

	/**
	 * @var array<\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord>
	 */
	private array $collection;


	public function __construct(
		KeyWord ...$entities,
	)
	{
		$this->collection = [];
		foreach ($entities as $keyWord) {
			$this->add($keyWord);
		}
	}


	public function add(
		KeyWord $keyWord,
	): void
	{
		$this->collection[$keyWord->value()] = $keyWord;
	}


	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}

}
