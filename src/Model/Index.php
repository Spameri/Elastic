<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class Index
{

	private \Spameri\Elastic\ClientProvider $clientProvider;

	private \Spameri\Elastic\Model\VersionProvider $versionProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider,
		\Spameri\Elastic\Model\VersionProvider $versionProvider
	)
	{
		$this->clientProvider = $clientProvider;
		$this->versionProvider = $versionProvider;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		array $data,
		string $index,
		?string $type = NULL
	): string
	{
		if ($type === NULL) {
			$type = $index;
		}

		try {
			$response = $this->clientProvider->client()->index(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($data),
						$type
					)
				)->toArray()
			)
			;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		try {
			$this->clientProvider->client()->indices()->refresh(
				(
					new \Spameri\ElasticQuery\Document($index)
				)
					->toArray()
			)
			;

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
