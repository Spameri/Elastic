<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Insert
{

	/**
	 * @var \Spameri\Elastic\Model\Insert\PrepareEntityArray
	 */
	private $prepareEntityArray;

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	public function __construct(
		\Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray
		, \Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->prepareEntityArray = $prepareEntityArray;
		$this->clientProvider = $clientProvider;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		\Spameri\Elastic\Entity\IElasticEntity $entity
		, string $index
	) : string
	{
		$entityArray = $this->prepareEntityArray->prepare($entity);
		unset($entityArray['id']);

		$document = new \Spameri\ElasticQuery\Document(
			$index,
			new \Spameri\ElasticQuery\Document\Body\Plain($entityArray),
			$entity->id()->value()
		);

		try {
			$response = $this->clientProvider->client()->index($document->toArray());

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		try {
			$this->clientProvider->client()->indices()->refresh(
				(
					new \Spameri\ElasticQuery\Document($index)
				)
					->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		if (\in_array($response['result'], ['created', 'updated'], TRUE)) {
			return $response['_id'];
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}

}
