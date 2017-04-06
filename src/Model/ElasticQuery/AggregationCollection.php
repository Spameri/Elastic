<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


class AggregationCollection implements ICollection
{

	/**
	 * @var Aggregation[]
	 */
	private $collection;


	public function __construct(
		Aggregation ... $collection
	)
	{
		$this->collection = $collection;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}


	public function add(Aggregation $aggregation)
	{
		$this->collection[] = $aggregation;
	}
}
