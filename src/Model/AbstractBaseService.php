<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

abstract class AbstractBaseService implements ServiceInterface
{

	public function __construct(
		protected readonly \Spameri\Elastic\Factory\EntityFactoryInterface $entityFactory,
		protected readonly \Spameri\Elastic\Factory\CollectionFactoryInterface $collectionFactory,
		protected readonly \Spameri\Elastic\Model\Insert $insert,
		protected readonly \Spameri\Elastic\Model\Get $get,
		protected readonly \Spameri\Elastic\Model\GetBy $getBy,
		protected readonly \Spameri\Elastic\Model\GetAllBy $getAllBy,
		protected readonly \Spameri\Elastic\Model\Delete $delete,
		protected readonly \Spameri\Elastic\Model\Aggregate $aggregate,
		protected readonly \Spameri\Elastic\EntityManager $entityManager,
		protected readonly \Spameri\Elastic\Settings\IndexConfigInterface $indexConfig,
	) {}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function insert(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): string
	{
		return $this->insert->execute($entity, $this->indexConfig->indexName(), $this->indexConfig->provide()->hasSti());
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function get(
		\Spameri\Elastic\Entity\Property\ElasticId $id,
	): \Spameri\Elastic\Entity\AbstractElasticEntity
	{
		try {
			$singleResult = $this->get->execute($id, $this->indexConfig->indexName());

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		if ( ! $singleResult->stats()->found()) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound(' with id ' . $id->value());
		}

		return $this->entityFactory->create($singleResult->hit(), $this->indexConfig->entityClass()[0], $this->entityManager);
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function getBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
	): \Spameri\Elastic\Entity\AbstractElasticEntity
	{
		try {
			$resultSearch = $this->getBy->execute($elasticQuery, $this->indexConfig->indexName());

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		if ($resultSearch->stats()->total() === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound($this->indexConfig->indexName(), $elasticQuery);
		}

		return $this->entityFactory->create($resultSearch->hits()->getIterator()->current(), $this->indexConfig->entityClass()[0], $this->entityManager);
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function getAllBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
	): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		try {
			$resultSearch = $this->getAllBy->execute($elasticQuery, $this->indexConfig->indexName());

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		if ($resultSearch->hits()->count() === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound($this->indexConfig->indexName(), $elasticQuery);
		}

		$entities = [];
		foreach ($resultSearch->hits() as $hit) {
			try {
				$entities[] = $this->entityFactory->create($hit, $this->indexConfig->entityClass()[0], $this->entityManager);

			} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
				\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);
			}
		}

		return $this->collectionFactory->create(
			$this->entityManager,
			$this->indexConfig->entityClass()[0],
			[],
			... $entities,
		);
	}


	/**
	 * @return \Spameri\Elastic\Entity\ElasticEntityCollectionInterface<\Spameri\Elastic\Entity\AbstractElasticEntity>
	 */
	public function createEmptyCollection(): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		return $this->collectionFactory->create(
			entityManager: $this->entityManager,
			entityClass: $this->indexConfig->entityClass()[0],
		);
	}


	public function delete(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): bool
	{
		try {
			return $this->delete->execute($id, $this->indexConfig->indexName());

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}
	}


	/**
	 * @param class-string $class
	 */
	public function cascadeDelete(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entityToDelete,
		string $class,
		string $field,
	): void
	{
		try {
			$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
			$elasticQuery->addMustQuery(
				new \Spameri\ElasticQuery\Query\Term(
					field: $field,
					query: $entityToDelete->id()->value(),
				),
			);
			$collection = $this->entityManager->findBy($elasticQuery, $class);
			foreach ($collection as $entity) {
				$this->entityManager->remove($entity);
			}

		} catch (\Spameri\Elastic\Exception\DocumentNotFound $e) {
			// Do nothing
		}
	}

	public function aggregate(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
	): \Spameri\ElasticQuery\Response\ResultSearch
	{
		return $this->aggregate->execute($elasticQuery, $this->indexConfig->indexName());
	}

}
