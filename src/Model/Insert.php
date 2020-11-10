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
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity
		, string $index
		, ?string $type = NULL
	): string
	{
		if ($type === NULL) {
			$type = $index;
		}

		$entityArray = $this->prepareEntityArray->prepare($entity);
		unset($entityArray['id']);

		$document = new \Spameri\ElasticQuery\Document(
			$index,
			new \Spameri\ElasticQuery\Document\Body\Plain($entityArray),
			$type,
			$entity->id()->value()
		);

		try {
			$documentArray = $document->toArray();
			if (\Spameri\Elastic\Model\VersionProvider::provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
				$documentArray['type'] = '_doc';
			}

			$response = $this->clientProvider->client()->index($documentArray);

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

		if (isset($response['created']) || isset($response['updated'])) {
			return $response['_id'];
		}

		if (isset($response['result']) && $response['result'] === 'created') {
			return $response['_id'];
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}

}
