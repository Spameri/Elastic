<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;


class ElasticIdCollection implements \Spameri\Elastic\Entity\ValueCollectionInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\Property\ElasticIdInterface>
	 */
	private $collection;


	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface ... $collection
	)
	{
		$this->collection = $collection;
	}


	public function add(\Spameri\Elastic\Entity\Property\ElasticIdInterface $featured): void
	{
		$this->collection[] = $featured;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}
