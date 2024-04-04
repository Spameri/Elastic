<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Index
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\Elastic\Model\VersionProvider $versionProvider,
	)
	{
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		array $data,
		string $index,
		string|null $type = NULL,
	): string
	{
		if ($type === NULL) {
			$type = $index;
		}

		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$type = NULL;
		}

		try {
			$response = $this->clientProvider->client()->index(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($data),
						$type,
					)
				)->toArray(),
			)->asArray()
			;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		try {
			$this->clientProvider->client()->indices()->refresh(
				(
					new \Spameri\ElasticQuery\Document($index)
				)
					->toArray(),
			)
			;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
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
