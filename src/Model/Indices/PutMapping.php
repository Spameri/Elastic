<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class PutMapping
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\Elastic\Model\VersionProvider $versionProvider,
	)
	{
	}


	/**
	 * @param array<mixed> $mapping
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		array $mapping,
		string $dynamic = 'false',
		string|null $type = NULL,
	): array
	{
		if ($type === NULL) {
			$type = $index;
		}
		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$properties = $mapping['properties'];
			$type = NULL;

		} else {
			$properties = \reset($mapping)['properties'];
		}

		try {
			return $this->clientProvider->client()->indices()->putMapping(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain(
						[
							'properties' => $properties,
							'dynamic' => $dynamic,
						],
					),
					$type,
				)
				)->toArray(),
			)->asArray()
				;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
