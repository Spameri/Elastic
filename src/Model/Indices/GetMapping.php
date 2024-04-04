<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class GetMapping
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\Elastic\Model\VersionProvider $versionProvider,
	)
	{
	}


	/**
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		string|null $type = NULL,
	): array
	{
		if ($type === NULL) {
			$type = $index;
		}

		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$type = NULL;
		}

		try {
			$documentArray = (
				new \Spameri\ElasticQuery\Document(
					$index,
					NULL,
					$type,
				)
			)->toArray();

			return $this->clientProvider->client()->indices()->getMapping($documentArray)->asArray();

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
