<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


abstract class AbstractValueCollection implements ValueCollectionInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\ValueInterface>
	 */
	protected $collection;


	public function __construct(
		\Spameri\Elastic\Entity\ValueInterface ... $collection
	)
	{
		$this->collection = [];
		foreach ($collection as $value) {
			$this->add($value);
		}
	}


	public function add(
		\Spameri\Elastic\Entity\ValueInterface $value
	) : void
	{
		$this->collection[$value->value()] = $value;
	}


	public function remove($key) : void
	{
		unset($this->collection[$key]);
	}


	public function get($key) : ?\Spameri\Elastic\Entity\ValueInterface
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
