<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


abstract class AbstractValueCollection implements IValueCollection
{

	/**
	 * @var array<\Spameri\Elastic\Entity\IValue>
	 */
	protected $collection;


	public function __construct(
		\Spameri\Elastic\Entity\IValue ... $collection
	)
	{
		$this->collection = [];
		foreach ($collection as $value) {
			$this->add($value);
		}
	}


	public function add(
		\Spameri\Elastic\Entity\IValue $value
	) : void
	{
		$this->collection[$value->value()] = $value;
	}


	public function remove($key) : void
	{
		unset($this->collection[$key]);
	}


	public function get($key) : ?\Spameri\Elastic\Entity\IValue
	{
		if ( ! isset($this->collection[$key])) {
			return NULL;
		}

		return $this->collection[$key];
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}

}
