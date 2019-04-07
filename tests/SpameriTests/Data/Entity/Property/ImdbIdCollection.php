<?php

namespace SpameriTests\Data\Entity\Property;


class ImdbIdCollection implements \Spameri\Elastic\Entity\IValueCollection
{

	/**
	 * @var \SpameriTests\Data\Entity\Property\ImdbId[]
	 */
	private $collection;


	public function __construct(
		ImdbId ... $collection
	)
	{
		$this->collection = [];
		foreach ($collection as $imdbId) {
			$this->add($imdbId);
		}
	}


	public function add(
		ImdbId $imdbId
	) : void
	{
		$this->collection[$imdbId->value()] = $imdbId;
	}


	public function find(
		ImdbId $imdbId
	) : ?ImdbId
	{
		foreach ($this->collection as $value) {
			if ($imdbId->value() === $value->value()) {
				return $value;
			}
		}

		return NULL;
	}


	public function keys() : array
	{
		return \array_keys($this->collection);
	}


	public function first() : ?ImdbId
	{
		return \reset($this->collection);
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}
