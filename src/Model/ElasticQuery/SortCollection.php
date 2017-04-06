<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


class SortCollection implements ICollection
{

	/**
	 * @var Sort[]
	 */
	private $collection;


	public function __construct(
		Sort ... $collection
	)
	{
		$this->collection = $collection;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}


	public function add(Sort $sort)
	{
		$this->collection[] = $sort;
	}
}
