<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


class FilterCollection implements ICollection
{

	/**
	 * @var Filter[]
	 */
	private $collection;


	public function __construct(
		Filter ... $collection
	)
	{
		$this->collection = $collection;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}


	public function add(Filter $filter)
	{
		$this->collection[] = $filter;
	}
}
