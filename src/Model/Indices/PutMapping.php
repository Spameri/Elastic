<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;


class PutMapping
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
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

		try {
			$documentArray = (
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain([
						'properties' => $mapping['properties'],
						'dynamic' => $dynamic,
					]),
					$type
				)
			)->toArray();

			if (\Spameri\Elastic\Model\VersionProvider::provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
				unset($documentArray['type']);
			}

			return $this->clientProvider->client()->indices()->putMapping($documentArray);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
