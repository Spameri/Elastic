<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapper;


class ElasticMapper
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	protected $clientProvider;
	/**
	 * @var \Kdyby\Clock\IDateTimeProvider
	 */
	protected $constantProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
		, \Kdyby\Clock\IDateTimeProvider $constantProvider
	)
	{
		$this->clientProvider = $clientProvider;
		$this->constantProvider = $constantProvider;
	}


	/**
	 * @param array $entity
	 * @throws \Exception
	 */
	public function createMapping($entity) : void
	{
		$this->clientProvider->client()->indices()->putMapping(
			(
				new \Spameri\ElasticQuery\Document(
					\Spameri\Elastic\Model\BaseService::ELASTIC_INDEX, // TODO pro každou entitu vlastní index
					new \Spameri\ElasticQuery\Document\Body\Plain([
						'dynamic' => $entity['dynamic'],
						'properties' => $entity['properties'],
					]),
					$entity['type']
				)
			)->toArray()
		);
	}


	/**
	 * @throws \Exception
	 */
	public function createIndex() : void
	{
		try {
			$this->clientProvider->client()->indices()->get(
				(
					new \Spameri\ElasticQuery\Document(
						\Spameri\Elastic\Model\BaseService::ELASTIC_INDEX
					)
				)->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $exception) {
			$indexName = \Spameri\Elastic\Model\BaseService::ELASTIC_INDEX . '-' . $this->constantProvider->getDateTime()->format('Y-m-d_H-i-s');
			$this->clientProvider->client()->indices()->create(
				(
					new \Spameri\ElasticQuery\Document(
						$indexName
					)
				)->toArray()
			);
			$this->clientProvider->client()->indices()->putAlias(
				(
					new \Spameri\ElasticQuery\Document(
						$indexName,
						new \Spameri\ElasticQuery\Document\Body\Plain([
							'actions' => [
								'add' => [
									'index' => $indexName,
									'alias' => \Spameri\Elastic\Model\BaseService::ELASTIC_INDEX,
								],
							],
						]),
						NULL,
						NULL,
						[
							'name' => \Spameri\Elastic\Model\BaseService::ELASTIC_INDEX,
						]
					)
				)->toArray()
			);
		}
	}


	public function deleteIndex()
	{
		try {
			$index = $this->clientProvider->client()->indices()->get(
				(
					new \Spameri\ElasticQuery\Document(
						\Spameri\Elastic\Model\BaseService::ELASTIC_INDEX
					)
				)->toArray()
			);
			if ($index) {
//				$response = $index->removeAlias($index->getName()); // TODO
//				$index->delete();
//				return $response;
			}

		} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $exception) {

		}

		return NULL;
	}
}
