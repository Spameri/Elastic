<?php declare(strict_types = 1);

namespace Spameri\Elastic;

readonly class EntityManager
{

	public function __construct(
		private \Spameri\Elastic\Model\Insert $insert,
		private \Spameri\Elastic\Model\GetAllBy $getAllBy,
		private \Spameri\Elastic\Model\Delete $delete,
		private \Spameri\Elastic\Model\EntitySettingsLocator $entitySettingsLocator,
		private \Spameri\Elastic\Factory\EntityFactory $entityFactory,
		private \Spameri\Elastic\EventManager $eventManager,
		private \Spameri\Elastic\EventManager\DispatchEvents $dispatchEvents,
		private \Spameri\Elastic\Model\IdentityMap $identityMap,
		private \Spameri\Elastic\Model\ChangeSet $changeSet,
	)
	{
	}


	/**
	 * @template T
	 * @param class-string<T> $class
	 * @param string $id
	 * @return T
	 */
	public function find(
		string $id,
		string $class,
	)
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
	 * @template T
	 * @param class-string<T> $class
	 * @return T
	 */
	public function findOneBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $class,
	)
	{
		$elasticQuery->options()->changeSize(1);

		$entities = $this->findBy($elasticQuery, $class);

		if (\count($entities) === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound('Entity ' . $class . ' not found.');
		}

		return \reset($entities);
	}


	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return array<T>
	 */
	public function findBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $class,
	): array
	{
		$indexConfig = $this->entitySettingsLocator->locateByEntityClass($class);

		try {
			$resultSearch = $this->getAllBy->execute($elasticQuery, $indexConfig->indexName());

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		$entities = [];
		foreach ($resultSearch->hits() as $hit) {
			try {
				$entities[] = $this->entityFactory->create(
					hit: $hit,
					class: $class,
					entityManager: $this,
				)->current();

			} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
				\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);
			}
		}

		// TODO univerzální kolekce
		return $entities;
	}

	/**
	 * @template T
	 * @param class-string<T> $class
	 * @return array<T>
	 */
	public function findAll(string $class): array
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
			return $success;
		}

		$this->eventManager->dispatch(
			event: EventManager::POST_DELETE,
			entityClass: $entity::class,
			entity: $entity,
		);
		$this->dispatchEvents->execute($entity, EventManager::POST_DELETE);

		return $success;
	}

}
