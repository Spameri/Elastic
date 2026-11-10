<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Collection;

/**
 * @template-covariant T of \Spameri\Elastic\Entity\AbstractElasticEntity
 * @template-implements \Spameri\Elastic\Entity\ElasticEntityCollectionInterface<T>
 */
abstract class AbstractElasticEntityCollection implements \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\AbstractElasticEntity>
	 */
	protected array $collection;

	protected bool $initialized;

	/**
	 * @var array<string>
	 */
	protected array $elasticIds;


	/**
	 * @param class-string $entityClass
	 */
	public function __construct(
		protected \Spameri\Elastic\EntityManager $entityManager,
		protected string $entityClass,
		public \Spameri\ElasticQuery\Response\ResultSearch $resultSearch = new \Spameri\ElasticQuery\Response\ResultSearch(
			new \Spameri\ElasticQuery\Response\Stats(0, false, 0),
			new \Spameri\ElasticQuery\Response\Shards(0, 1, 0, 0),
			new \Spameri\ElasticQuery\Response\Result\HitCollection(),
			new \Spameri\ElasticQuery\Response\Result\AggregationCollection(),
		),
		\Spameri\Elastic\Entity\AbstractElasticEntity ...$entityCollection,
	)
	{
		$this->collection = [];
		$this->initialized = false;
		$this->elasticIds = $resultSearch->hits()->ids();

		if (
			\count($entityCollection) > 0
		) {
			$this->initialized = true;
		}

		foreach ($entityCollection as $elasticEntity) {
			$this->add($elasticEntity);
		}
	}


	public function add(
		\Spameri\Elastic\Entity\AbstractElasticEntity $elasticEntity,
	): void
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		if ($elasticEntity->id() instanceof \Spameri\Elastic\Entity\Property\ElasticId) {
			$this->collection[$elasticEntity->id()->value()] = $elasticEntity;
		} else {
			$this->collection[] = $elasticEntity;
		}
	}


	public function initialize(): void
	{
		$this->initialized = true;

		if ( ! $this->elasticIds) {
			return;
		}

		// Check IdentityMap for already-cached entities
		$uncachedIds = [];
		foreach ($this->elasticIds as $id) {
			$cached = $this->entityManager->findCached($id, $this->entityClass);
			if ($cached !== null) {
				$this->collection[$cached->id()->value()] = $cached;
			} else {
				$uncachedIds[] = $id;
			}
		}

		if (\count($uncachedIds) === 0) {
			return;
		}

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery(
			new \Spameri\ElasticQuery\Query\QueryCollection(
				null,
				new \Spameri\ElasticQuery\Query\MustCollection(
					new \Spameri\ElasticQuery\Query\Terms(
						'_id',
						$uncachedIds,
					),
				),
			),
		);
		$elasticQuery->options()->changeSize(\count($uncachedIds));

		$entities = $this->entityManager->findBy(
			$elasticQuery,
			$this->entityClass,
		);

		foreach ($entities as $entity) {
			if ($entity->id() instanceof \Spameri\Elastic\Entity\Property\ElasticId) {
				$this->collection[$entity->id()->value()] = $entity;
			} else {
				$this->collection[] = $entity;
			}
		}
	}


	public function initialized(): bool
	{
		return $this->initialized;
	}


	public function elasticIds(): array
	{
		return $this->elasticIds;
	}


	/**
	 * @return class-string
	 */
	public function entityClass(): string
	{
		return $this->entityClass;
	}


	/**
	 * @param array<string> $ids
	 */
	public function setElasticIds(array $ids): void
	{
		$this->elasticIds = $ids;
		$this->initialized = false;
	}


	/**
	 * @return array<T>
	 */
	protected function collection(): array
	{
		return $this->collection;
	}


	/**
	 * @return \ArrayIterator<int|string, \Spameri\Elastic\Entity\AbstractElasticEntity>
	 */
	public function getIterator(): \ArrayIterator
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		return new \ArrayIterator($this->collection);
	}


	public function entity(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): \Spameri\Elastic\Entity\AbstractElasticEntity|null
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		if ($id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return null;
		}

		if ($id->value() && \array_key_exists($id->value(), $this->keys())) {
			return $this->collection[$id->value()];
		}

		return null;
	}


	public function remove(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): void
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		unset($this->collection[$id->value()]);
	}


	public function isValue(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): bool
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		return \array_key_exists($id->value(), $this->keys());
	}


	public function count(): int
	{
		if ( ! $this->initialized) {
			return \count($this->elasticIds);
		}

		return \count($this->collection);
	}


	public function keys(): array
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		return \array_map('\strval', \array_keys($this->collection));
	}


	public function isKey(
		string $key,
	): bool
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		return \array_key_exists($key, \array_map('\strval', \array_keys($this->collection)));
	}


	public function clear(): void
	{
		$this->collection = [];
	}


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField, // phpcs:ignore
		string $type,
	): void
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		if ( ! \in_array($type, ['asc', 'desc'], true)) {
			throw new \Nette\InvalidArgumentException('Not supported sorting method.');
		}

		throw new \Nette\NotImplementedException();
	}


	public function first(): \Spameri\Elastic\Entity\AbstractElasticEntity|null
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		return \reset($this->collection) ?: null;
	}

	public function __serialize(): array
	{
		return [
			'collection' => $this->collection,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->collection = $data;
	}

}
