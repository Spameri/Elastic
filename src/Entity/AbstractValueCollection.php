<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

/**
 * @template-covariant T of \Spameri\Elastic\Entity\ValueInterface
 * @template-implements \Spameri\Elastic\Entity\ValueCollectionInterface<T>
 */
abstract class AbstractValueCollection implements ValueCollectionInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\ValueInterface>
	 */
	protected array $collection;


	public function __construct(
		\Spameri\Elastic\Entity\ValueInterface ...$collection,
	)
	{
		$this->collection = [];
		foreach ($collection as $value) {
			$this->add($value);
		}
	}


	public function add(
		\Spameri\Elastic\Entity\ValueInterface $value,
	): void
	{
		$this->collection[$value->value()] = $value;
	}


	public function remove(mixed $key): void
	{
		unset($this->collection[$key]);
	}


	public function get(mixed $key): \Spameri\Elastic\Entity\ValueInterface|null
	{
		if ( ! isset($this->collection[$key])) {
			return null;
		}

		return $this->collection[$key];
	}


	/**
	 * @return \ArrayIterator<int|string, \Spameri\Elastic\Entity\ValueInterface>
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}

}
