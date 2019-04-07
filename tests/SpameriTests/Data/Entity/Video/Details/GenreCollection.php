<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Details;


class GenreCollection implements \Spameri\Elastic\Entity\IValueCollection
{

	/**
	 * @var array<\SpameriTests\Data\Entity\Video\Details\Genre>
	 */
	private $collection;


	public function __construct(
		Genre ... $collection
	)
	{
		$this->collection = [];
		foreach ($collection as $genre) {
			$this->add($genre);
		}
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}


	public function add(
		Genre $genre
	) : void
	{
		$this->collection[$genre->value()] = $genre;
	}
}
