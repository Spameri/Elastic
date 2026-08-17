<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Collection;

/**
 * @template-covariant T of \Spameri\Elastic\Entity\EntityInterface
 * @template-implements \Spameri\Elastic\Entity\EntityCollectionInterface<T>
 */
abstract class AbstractEntityCollection implements \Spameri\Elastic\Entity\EntityCollectionInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\EntityInterface>
	 */
	protected array $collection;

	protected bool $lazyInitialized = true;

	protected \Closure|null $lazyInitializer = null;


	public function __construct(
		\Spameri\Elastic\Entity\EntityInterface ...$collection,
	)
	{
		$this->collection = [];
		foreach ($collection as $elasticEntity) {
			$this->add($elasticEntity);
		}
	}


	public function setInitializer(\Closure $initializer): void
	{
		$this->lazyInitializer = $initializer;
		$this->lazyInitialized = false;
	}


	public function ensureInitialized(): void
	{
		if ($this->lazyInitialized) {
			return;
		}

		$this->lazyInitialized = true;
		$initializer = $this->lazyInitializer;
		$this->lazyInitializer = null;

		if ($initializer !== null) {
			$initializer();
		}
	}


	public function initialized(): bool
	{
		return $this->lazyInitialized;
	}


	public function add(
		\Spameri\Elastic\Entity\EntityInterface $entity,
	): void
	{
		$this->ensureInitialized();
		$this->collection[$entity->key()] = $entity;
	}


	/**
	 * @return array<T>
	 */
	protected function collection(): array
	{
		return $this->collection;
	}


	/**
	 * @return \ArrayIterator<int|string, \Spameri\Elastic\Entity\EntityInterface>
	 */
	public function getIterator(): \ArrayIterator
	{
		$this->ensureInitialized();

		return new \ArrayIterator($this->collection);
	}


	public function entity(
		string $key,
	): \Spameri\Elastic\Entity\EntityInterface|null
	{
		$this->ensureInitialized();

		if (\array_key_exists($key, $this->collection)) {
			return $this->collection[$key];
		}

		return null;
	}


	public function remove(
		string|int $key,
	): void
	{
		$this->ensureInitialized();
		unset($this->collection[$key]);
	}


	public function isValue(
		string $key,
	): bool
	{
		$this->ensureInitialized();

		return \array_key_exists($key, $this->collection);
	}


	public function count(): int
	{
		$this->ensureInitialized();

		return \count($this->collection);
	}


	public function keys(): array
	{
		$this->ensureInitialized();

		return \array_map('\strval', \array_keys($this->collection));
	}


	public function isKey(
		string $key,
	): bool
	{
		$this->ensureInitialized();

		return \array_key_exists($key, \array_map('\strval', \array_keys($this->collection)));
	}


	public function clear(): void
	{
		$this->collection = [];
		$this->lazyInitialized = true;
		$this->lazyInitializer = null;
	}


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField, // phpcs:ignore
		string $type,
	): void
	{
		$this->ensureInitialized();

		if ( ! \in_array($type, ['asc', 'desc'], true)) {
			throw new \Nette\InvalidArgumentException('Not supported sorting method.');
		}

		throw new \Nette\NotImplementedException();
	}


	public function first(): \Spameri\Elastic\Entity\EntityInterface|null
	{
		$this->ensureInitialized();

		return \reset($this->collection) ?: null;
	}

}
