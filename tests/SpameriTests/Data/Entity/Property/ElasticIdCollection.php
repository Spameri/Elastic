<?php

namespace SpameriTests\Data\Entity\Property;


class ElasticIdCollection implements \Spameri\Elastic\Entity\IValueCollection
{

	/**
	 * @var \Spameri\Elastic\Entity\Property\IElasticId[]
	 */
	private $collection;


	public function __construct(
		\Spameri\Elastic\Entity\Property\IElasticId ... $collection
	)
	{
		$this->collection = $collection;
	}


	public function add(\Spameri\Elastic\Entity\Property\IElasticId $featured)
	{
		$this->collection[] = $featured;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}
