<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


abstract class BaseService implements IService
{

	/**
	 * @var string
	 */
	protected $index;

	/**
	 * @var \Elasticsearch\Client
	 */
	protected $client;

	/**
	 * @var \Spameri\Elastic\Model\Insert
	 */
	protected $insert;

	/**
	 * @var \Spameri\Elastic\Model\Get
	 */
	protected $get;

	/**
	 * @var \Spameri\Elastic\Model\Delete
	 */
	protected $delete;

	/**
	 * @var \Spameri\Elastic\Model\GetBy
	 */
	protected $getBy;

	/**
	 * @var \Spameri\Elastic\Model\GetAllBy
	 */
	protected $getAllBy;

	/**
	 * @var array
	 */
	protected $entityProperties;

	/**
	 * @var \Spameri\Elastic\Factory\IEntityFactory
	 */
	protected $entityFactory;

	/**
	 * @var \Spameri\Elastic\Factory\ICollectionFactory
	 */
	protected $collectionFactory;

	/**
	 * @var \Spameri\Elastic\Model\Aggregate
	 */
	protected $aggregate;


	public function __construct(
		string $index
		, array $entityProperties
		, \Spameri\Elastic\Factory\IEntityFactory $entityFactory
		, \Spameri\Elastic\Factory\ICollectionFactory $collectionFactory
		, \Spameri\Elastic\ClientProvider $client
		, Insert $insert
		, Get $get
		, GetBy $getBy
		, GetAllBy $getAllBy
		, Delete $delete
		, Aggregate $aggregate
	)
	{
		$this->client = $client->client();
		$this->index = $index;
		$this->insert = $insert;
		$this->get = $get;
		$this->delete = $delete;
		$this->getBy = $getBy;
		$this->getAllBy = $getAllBy;
		$this->entityProperties = $entityProperties;
		$this->entityFactory = $entityFactory;
		$this->collectionFactory = $collectionFactory;
		$this->aggregate = $aggregate;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function insert(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : string
	{
		return $this->insert->execute($entity, $this->index);
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function get(
		\Spameri\Elastic\Entity\Property\ElasticId $id
	) : \Spameri\Elastic\Entity\IElasticEntity
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
	) : \Spameri\Elastic\Entity\IElasticEntity
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
	) : \Spameri\Elastic\Entity\IElasticEntityCollection
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
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : bool
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
	) : \Spameri\ElasticQuery\Response\ResultSearch
	{
		return $this->aggregate->execute($elasticQuery, $this->index);
	}

}
