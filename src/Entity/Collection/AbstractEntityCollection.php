<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Collection;

abstract class AbstractEntityCollection implements \Spameri\Elastic\Entity\EntityCollectionInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\EntityInterface>
	 */
	private $collection;


	public function __construct(
		\Spameri\Elastic\Entity\EntityInterface ...$entityCollection
	)
	{
		$this->collection = [];
		foreach ($entityCollection as $elasticEntity) {
			$this->add($elasticEntity);
		}
	}


	public function add(
		\Spameri\Elastic\Entity\EntityInterface $entity
	): void
	{
		$this->collection[$entity->key()] = $entity;
	}


	protected function collection(): array
	{
		return $this->collection;
	}


	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}


	public function entity(
		string $key
	): ?\Spameri\Elastic\Entity\EntityInterface
	{
		if (\array_key_exists($key, $this->collection)) {
			return $this->collection[$key];
		}

		return NULL;
	}


	public function remove(
		string $key
	): void
	{
		unset($this->collection[$key]);
	}


	public function isValue(
		string $key
	): bool
	{
		return \array_key_exists($key, $this->collection);
	}


	public function count(): int
	{
		return \count($this->collection);
	}


	public function keys(): array
	{
		return \array_map('\strval', \array_keys($this->collection));
	}


	public function isKey(
		string $key
	): bool
	{
		return \array_key_exists($key, \array_map('\strval', \array_keys($this->collection)));
	}


	public function clear(): void
	{
		$this->collection = [];
	}


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField, // phpcs:ignore
		string $type
	): void
	{
		if ( ! \in_array($type, ['asc', 'desc'], TRUE)) {
			throw new \Nette\InvalidArgumentException('Not supported sorting method.');
		}

		throw new \Nette\NotImplementedException();
	}

}
