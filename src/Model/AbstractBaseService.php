<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

abstract class AbstractBaseService implements ServiceInterface
{

	public function __construct(
		public string $index,
		protected readonly \Spameri\Elastic\Factory\EntityFactoryInterface $entityFactory,
		protected readonly \Spameri\Elastic\Factory\CollectionFactoryInterface $collectionFactory,
		protected readonly \Spameri\Elastic\Model\Insert $insert,
		protected readonly \Spameri\Elastic\Model\Get $get,
		protected readonly \Spameri\Elastic\Model\GetBy $getBy,
		protected readonly \Spameri\Elastic\Model\GetAllBy $getAllBy,
		protected readonly \Spameri\Elastic\Model\Delete $delete,
		protected readonly \Spameri\Elastic\Model\Aggregate $aggregate,
		protected readonly \Spameri\Elastic\Model\ServiceLocator $serviceLocator,
	) {}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function insert(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity
	): string
	{
		return $this->insert->execute($entity, $this->index);
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function get(
		\Spameri\Elastic\Entity\Property\ElasticId $id
	): \Spameri\Elastic\Entity\ElasticEntityInterface
	{
		try {
			$singleResult = $this->get->execute($id, $this->index);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		if ( ! $singleResult->stats()->found()) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound(' with id ' . $id->value());
		}

		return $this->entityFactory->create($singleResult->hit())->current();
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function getBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	): \Spameri\Elastic\Entity\ElasticEntityInterface
	{
		try {
			$resultSearch = $this->getBy->execute($elasticQuery, $this->index);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		if ($resultSearch->stats()->total() === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound($this->index, $elasticQuery);
		}

		return $this->entityFactory->create($resultSearch->hits()->getIterator()->current())->current();
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function getAllBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		try {
			$resultSearch = $this->getAllBy->execute($elasticQuery, $this->index);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		if ($resultSearch->hits()->count() === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound($this->index, $elasticQuery);
		}

		$entities = [];
		foreach ($resultSearch->hits() as $hit) {
			try {
				$entities[] = $this->entityFactory->create($hit)->current();

			} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
				\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);
			}
		}

		return $this->collectionFactory->create(
			$this,
			[],
			... $entities
		);
	}


	public function createEmptyCollection(): \Spameri\Elastic\Entity\ElasticEntityCollectionInterface
	{
		return $this->collectionFactory->create($this);
	}


	public function delete(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id
	): bool
	{
		try {
			return $this->delete->execute($id, $this->index);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}
	}

	public function deleteReference(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entityToDelete,
		string $class,
		string $field,
	): void
	{
		try {
			$service = $this->serviceLocator->locateByEntityClass($class);
			$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
			$elasticQuery->addMustQuery(
				new \Spameri\ElasticQuery\Query\Term(
					field: $field,
					query: $entityToDelete->id()->value()
				)
			);
			$collection = $service->getAllBy($elasticQuery);
			foreach ($collection as $entity) {
				$service->delete($entity->id());
			}

		} catch (\Spameri\Elastic\Exception\DocumentNotFound $e) {
			// Do nothing
		}
	}

	public function aggregate(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	): \Spameri\ElasticQuery\Response\ResultSearch
	{
		return $this->aggregate->execute($elasticQuery, $this->index);
	}

}
