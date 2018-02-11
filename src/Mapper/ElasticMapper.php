<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapper;


class ElasticMapper
{

	/**
	 * @var \Kdyby\ElasticSearch\Client
	 */
	protected $client;
	/**
	 * @var \Kdyby\Clock\IDateTimeProvider
	 */
	protected $constantProvider;


	public function __construct(
		\Kdyby\ElasticSearch\Client $client
		, \Kdyby\Clock\IDateTimeProvider $constantProvider
	)
	{
		$this->client = $client;
		$this->constantProvider = $constantProvider;
	}


	/**
	 * @param array $entity
	 * @throws \Exception
	 */
	public function createMapping($entity)
	{
		$elasticType = $this->client->getIndex(\Spameri\Elastic\Model\BaseService::ELASTIC_INDEX)->getType($entity['type']);

		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType($elasticType);

		$mapping->setParam('dynamic', $entity['dynamic']);
		$mapping->setProperties(
			$entity['properties']
		);

		$mapping->send();
	}


	/**
	 * @throws \Exception
	 */
	public function createIndex()
	{
		$result = $this->client->request(\Spameri\Elastic\Model\BaseService::ELASTIC_INDEX, \Elastica\Request::HEAD);
		if ($result->getStatus() !== \Nette\Http\Response::S200_OK) {
			$indexName = \Spameri\Elastic\Model\BaseService::ELASTIC_INDEX . '-' . $this->constantProvider->getDateTime()->format('Y-m-d_H-i-s');
			$index = $this->client->getIndex($indexName);
			$index->create([
				'number_of_shards'   => 5,
				'number_of_replicas' => 1,
			]);
			$index->addAlias(\Spameri\Elastic\Model\BaseService::ELASTIC_INDEX);
		}
	}


	public function deleteIndex() : void
	{
		try {
			$index = $this->client->getIndex(\Spameri\Elastic\Model\BaseService::ELASTIC_INDEX);
			if ($index) {
				$index->delete();
			}

		} catch (\Elastica\Exception\ResponseException $exception) {

		}
	}
}
