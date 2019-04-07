<?php

namespace SpameriTests\Data\Entity\Video\Story;


class KeyWordCollection implements \Spameri\Elastic\Entity\IValueCollection
{

	/**
	 * @var KeyWord[]
	 */
	private $collection;


	public function __construct(
		KeyWord ... $entities
	)
	{
		$this->collection = [];
		foreach ($entities as $keyWord) {
			$this->add($keyWord);
		}
	}


	public function add(
		KeyWord $keyWord
	) : void
	{
		$this->collection[$keyWord->value()] = $keyWord;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}