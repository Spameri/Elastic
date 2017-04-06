<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


class QueryCollection implements ICollection
{

	/**
	 * @var Query[]
	 */
	private $collection;


	public function __construct(
		Query ... $collection
	)
	{
		$this->collection = $collection;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}


	public function add(Query $query)
	{
		$this->collection[] = $query;
	}
}
