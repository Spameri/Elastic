<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Collection;

abstract class AbstractElasticEntityCollection implements \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
{

	/**
	 * @var array<\Spameri\Elastic\Entity\ElasticEntityInterface>
	 */
	protected $collection;

	/**
	 * @var \Spameri\Elastic\Model\ServiceInterface
	 */
	protected $service;

	/**
	 * @var array<string>
	 */
	protected $elasticIds;

	/**
	 * @var bool
	 */
	protected $initialized;


	public function __construct(
		\Spameri\Elastic\Model\ServiceInterface $service,
		array $elasticIds = [],
		\Spameri\Elastic\Entity\ElasticEntityInterface ...$entityCollection,
	)
	{
		$this->collection = [];
		$this->service = $service;
		$this->elasticIds = $elasticIds;
		$this->initialized = FALSE;

		if (
			! $elasticIds
			&& \count($entityCollection) > 0
		) {
			$this->initialized = TRUE;
		}

		foreach ($entityCollection as $elasticEntity) {
			$this->add($elasticEntity);
		}
	}


	public function add(
		\Spameri\Elastic\Entity\ElasticEntityInterface $elasticEntity,
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
		if ($this->elasticIds) {
			$entities = $this->service->getAllBy(
				new \Spameri\ElasticQuery\ElasticQuery(
					new \Spameri\ElasticQuery\Query\QueryCollection(
						NULL,
						new \Spameri\ElasticQuery\Query\MustCollection(
							new \Spameri\ElasticQuery\Query\Terms(
								'_id',
								$this->elasticIds,
							),
						),
					),
				),
			);

			$this->initialized = TRUE;

			foreach ($entities as $entity) {
				$this->add($entity);
			}
		} else {
			$this->initialized = TRUE;
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


	protected function collection(): array
	{
		return $this->collection;
	}


	public function getIterator(): \ArrayIterator
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		return new \ArrayIterator($this->collection);
	}


	public function entity(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): \Spameri\Elastic\Entity\ElasticEntityInterface|null
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		if ($id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return NULL;
		}

		if ($id->value() && \array_key_exists($id->value(), $this->keys())) {
			return $this->collection[$id->value()];
		}

		return NULL;
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
			$this->initialize();
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

		if ( ! \in_array($type, ['asc', 'desc'], TRUE)) {
			throw new \Nette\InvalidArgumentException('Not supported sorting method.');
		}

		throw new \Nette\NotImplementedException();
	}


	public function first(): \Spameri\Elastic\Entity\ElasticEntityInterface|null
	{
		if ( ! $this->initialized) {
			$this->initialize();
		}

		return \reset($this->collection) ?: NULL;
	}

}
