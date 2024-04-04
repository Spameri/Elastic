<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Delete
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private VersionProvider $versionProvider,
	)
	{
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function execute(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		string $index,
		string|null $type = NULL,
	): bool
	{
		if ($type === NULL) {
			$type = $index;
		}

		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$type = NULL;
		}

		try {
			$response = $this->clientProvider->client()->delete(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					NULL,
					$type,
					$id->value(),
				)
				)
					->toArray(),
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

		return $response['result'] === 'deleted';
	}

}
