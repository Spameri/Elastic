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
	private $collectionFactory;


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
	) {
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
	}


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
			$data = $this->get->execute($id, $this->index);

			if ($data) {
				$resultCollection = new \Spameri\Elastic\Entity\Collection\ResultCollection($data);

				return $this->entityFactory->create($resultCollection)->current();
			}

		} catch (\Spameri\Elastic\Exception\DocumentNotFound $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::ERROR);
		}
		throw new \Spameri\Elastic\Exception\DocumentNotFound(' with id ' . $id->value());
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function getBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\IElasticEntity
	{
		try {
			$data = $this->getBy->execute($elasticQuery, $this->index);

			if ($data) {
				$resultCollection = new \Spameri\Elastic\Entity\Collection\ResultCollection($data);

				return $this->entityFactory->create($resultCollection)->current();
			}
		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

		} catch (\Spameri\Elastic\Exception\DocumentNotFound $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::ERROR);
		}

		try {
			$queryString = \Nette\Utils\Json::encode($elasticQuery->toArray());

		} catch (\Nette\Utils\JsonException $exception) {
			$queryString = 'not valid json';
		}

		throw new \Spameri\Elastic\Exception\DocumentNotFound(' with query ' . $queryString);
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function getAllBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : \Spameri\Elastic\Entity\IElasticEntityCollection
	{
		try {
			$documents = $this->getAllBy->execute($elasticQuery, $this->index);

			if ($documents) {
				$resultCollection = new \Spameri\Elastic\Entity\Collection\ResultCollection($documents);
				$result = $this->collectionFactory->create(
					$this,
					NULL,
					... $this->entityFactory->create($resultCollection)
				);

			} else {
				$result = $this->collectionFactory->create(
					$this
				);
			}

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw new \Spameri\Elastic\Exception\DocumentNotFound($this->index);
		}

		return $result;
	}


	public function delete(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : bool
	{
		return $this->delete->execute($id, $this->index);
	}
}
