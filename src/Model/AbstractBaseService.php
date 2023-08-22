<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

abstract class AbstractBaseService implements ServiceInterface
{

	public string $index;

	protected \Spameri\Elastic\Model\Insert $insert;

	protected \Spameri\Elastic\Model\Get $get;

	protected \Spameri\Elastic\Model\Delete $delete;

	protected \Spameri\Elastic\Model\GetBy $getBy;

	protected \Spameri\Elastic\Model\GetAllBy $getAllBy;

	protected \Spameri\Elastic\Factory\EntityFactoryInterface $entityFactory;

	protected \Spameri\Elastic\Factory\CollectionFactoryInterface $collectionFactory;

	protected \Spameri\Elastic\Model\Aggregate $aggregate;


	public function __construct(
		string $index,
		\Spameri\Elastic\Factory\EntityFactoryInterface $entityFactory,
		\Spameri\Elastic\Factory\CollectionFactoryInterface $collectionFactory,
		\Spameri\Elastic\Model\Insert $insert,
		\Spameri\Elastic\Model\Get $get,
		\Spameri\Elastic\Model\GetBy $getBy,
		\Spameri\Elastic\Model\GetAllBy $getAllBy,
		\Spameri\Elastic\Model\Delete $delete,
		\Spameri\Elastic\Model\Aggregate $aggregate
	)
	{
		$this->index = $index;
		$this->insert = $insert;
		$this->get = $get;
		$this->delete = $delete;
		$this->getBy = $getBy;
		$this->getAllBy = $getAllBy;
		$this->entityFactory = $entityFactory;
		$this->collectionFactory = $collectionFactory;
		$this->aggregate = $aggregate;
	}


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

		if ($resultSearch->stats()->total() === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound($this->index, $elasticQuery);
		}

		$entities = [];
		foreach ($resultSearch->hits() as $hit) {
			$entities[] = $this->entityFactory->create($hit)->current();
		}

		return $this->collectionFactory->create(
			$this,
			[],
			... $entities
		);
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


	public function aggregate(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	): \Spameri\ElasticQuery\Response\ResultSearch
	{
		return $this->aggregate->execute($elasticQuery, $this->index);
	}

}
