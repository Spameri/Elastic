<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;


class PutMapping
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;

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
	 * @param array<mixed> $mapping
	 * @return array<mixed>
	 */
	public function execute(
		string $index
		, array $mapping
		, string $dynamic = 'false'
		, ?string $type = NULL
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
						new \Spameri\ElasticQuery\Document\Body\Plain([
							'properties' => $properties,
							'dynamic' => $dynamic,
						]),
						$type
					)
				)->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
