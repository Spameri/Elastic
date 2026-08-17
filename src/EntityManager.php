<?php declare(strict_types = 1);

namespace Spameri\Elastic;

readonly class EntityManager
{

	public function __construct(
		private \Spameri\Elastic\Model\Insert $insert,
		private \Spameri\Elastic\Model\GetAllBy $getAllBy,
		private \Spameri\Elastic\Model\Delete $delete,
		private \Spameri\Elastic\Model\EntitySettingsLocator $entitySettingsLocator,
		private \Spameri\Elastic\EventManager $eventManager,
		private \Spameri\Elastic\EventManager\DispatchEvents $dispatchEvents,
		private \Spameri\Elastic\Model\IdentityMap $identityMap,
		private \Spameri\Elastic\Model\ChangeSet $changeSet,
		private \Spameri\Elastic\Factory\EntityFactory $entityFactory,
	)
	{
	}


	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @param string $id
	 * @return T
	 */
	public function find(
		string $id,
		string $class,
	): \Spameri\Elastic\Entity\AbstractElasticEntity|null
	{
		$entity = $this->identityMap->get(
			class: $class,
			id: $id,
		);
		if ($entity !== null) {
			return $entity;
		}

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term(
				'_id',
				$id,
			),
		);

		return $this->findOneBy($elasticQuery, $class);
	}


	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @return T|null
	 */
	public function findOneBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $class,
	): \Spameri\Elastic\Entity\AbstractElasticEntity|null
	{
		$elasticQuery->options()->changeSize(1);

		$entities = $this->findBy($elasticQuery, $class);

		if ($entities->count() === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound('Entity ' . $class . ' not found.');
		}

		return $entities->first();
	}


	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @return \Spameri\Elastic\Entity\Collection\ElasticEntityCollection<T>
	 */
	public function findBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $class,
	): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
	{
		$indexConfig = $this->entitySettingsLocator->locateByEntityClass($class);

		try {
			$resultSearch = $this->getAllBy->execute($elasticQuery, $indexConfig->indexName());

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		return $this->createCollection($class, $resultSearch);
	}


	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @return \Spameri\Elastic\Entity\Collection\ElasticEntityCollection<T>
	 */
	public function createEmptyCollection(
		string $class,
	): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
	{
		return new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			entityManager: $this,
			entityClass: $class,
		);
	}


	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @return \Spameri\Elastic\Entity\Collection\ElasticEntityCollection<T>
	 */
	protected function createCollection(
		string $class,
		\Spameri\ElasticQuery\Response\ResultSearch $resultSearch,
	): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
	{
		$entities = [];
		foreach ($resultSearch->hits() as $hit) {
			try {
				$entities[] = $this->entityFactory->create($hit, $class, $this);

			} catch (\Spameri\Elastic\Exception\ElasticSearch|\Spameri\Elastic\Exception\DocumentNotFound $exception) {
				\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);
			}
		}

		return new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$this,
			$class,
			$resultSearch,
			... $entities,
		);
	}

	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @return \Spameri\Elastic\Entity\Collection\ElasticEntityCollection<T>
	 */
	public function findAll(string $class): \Spameri\Elastic\Entity\Collection\ElasticEntityCollection
	{
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(10000);

		return $this->findBy($elasticQuery, $class);
	}


	public function persist(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): string
	{
		$indexConfig = $this->entitySettingsLocator->locateByEntityClass($entity::class);

		$this->eventManager->dispatch(
			event: EventManager::PRE_PERSIST,
			entityClass: $entity::class,
			entity: $entity,
		);
		$this->dispatchEvents->execute($entity, EventManager::PRE_PERSIST);

		$id = $this->insert->execute(
			entity: $entity,
			index: $indexConfig->indexName(),
			hasSti: $indexConfig->hasSti(),
		);

		$this->eventManager->dispatch(
			event: EventManager::POST_PERSIST,
			entityClass: $entity::class,
			entity: $entity,
		);

		if ($this->changeSet->isExisting($entity) === false) {
			$this->eventManager->dispatch(
				event: EventManager::POST_CREATE,
				entityClass: $entity::class,
				entity: $entity,
			);

		} else {
			$this->eventManager->dispatch(
				event: EventManager::POST_UPDATE,
				entityClass: $entity::class,
				entity: $entity,
			);
			$this->dispatchEvents->execute($entity, EventManager::POST_UPDATE);
		}

		$this->dispatchEvents->execute($entity, EventManager::POST_CREATE);
		$this->dispatchEvents->execute($entity, EventManager::POST_PERSIST);

		return $id;
	}


	public function remove(\Spameri\Elastic\Entity\AbstractElasticEntity $entity): bool
	{
		$indexConfig = $this->entitySettingsLocator->locateByEntityClass($entity::class);

		$this->eventManager->dispatch(
			event: EventManager::PRE_DELETE,
			entityClass: $entity::class,
			entity: $entity,
		);
		$this->dispatchEvents->execute($entity, EventManager::PRE_DELETE);

		$success = $this->delete->execute($entity->id(), $indexConfig->indexName());

		if ($success === false) {
			return false;
		}

		$this->identityMap->remove($entity);

		$this->eventManager->dispatch(
			event: EventManager::POST_DELETE,
			entityClass: $entity::class,
			entity: $entity,
		);
		$this->dispatchEvents->execute($entity, EventManager::POST_DELETE);

		return true;
	}


	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @return T|null
	 */
	public function findCached(
		string $id,
		string $class,
	): \Spameri\Elastic\Entity\AbstractElasticEntity|null
	{
		return $this->identityMap->get(
			class: $class,
			id: $id,
		);
	}


	/**
	 * Preloads related entities for all items in a collection to avoid N+1 queries.
	 * Collects elastic IDs from uninitialized ElasticEntityCollection properties,
	 * groups them by entity class, and loads each group in a single bulk query.
	 *
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param \Spameri\Elastic\Entity\Collection\ElasticEntityCollection<T> $entities
	 */
	public function preloadRelations(
		\Spameri\Elastic\Entity\Collection\ElasticEntityCollection $entities,
	): void
	{
		/** @var array<class-string, array<string, true>> $idsByClass */
		$idsByClass = [];

		foreach ($entities as $entity) {
			foreach ($entity->entityVariables() as $property) {
				if (
					$property instanceof \Spameri\Elastic\Entity\Collection\AbstractElasticEntityCollection
					&& ! $property->initialized()
					&& \count($property->elasticIds()) > 0
				) {
					$class = $property->entityClass();
					foreach ($property->elasticIds() as $id) {
						$idsByClass[$class][$id] = true;
					}
				}
			}
		}

		foreach ($idsByClass as $class => $ids) {
			$idsArray = \array_keys($ids);

			// Skip IDs already in IdentityMap
			$uncachedIds = [];
			foreach ($idsArray as $id) {
				if ($this->identityMap->get($class, $id) === null) {
					$uncachedIds[] = $id;
				}
			}

			if (\count($uncachedIds) === 0) {
				continue;
			}

			$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery(
				new \Spameri\ElasticQuery\Query\QueryCollection(
					null,
					new \Spameri\ElasticQuery\Query\MustCollection(
						new \Spameri\ElasticQuery\Query\Terms('_id', $uncachedIds),
					),
				),
			);
			$elasticQuery->options()->changeSize(\count($uncachedIds));

			try {
				$this->findBy($elasticQuery, $class);
			} catch (\Throwable $exception) {
				\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);
			}
		}
	}


	public function clear(): void
	{
		$this->identityMap->clear();
		$this->changeSet->clear();
	}

}
