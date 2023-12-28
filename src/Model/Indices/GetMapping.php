<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

class GetMapping
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;

	private \Spameri\Elastic\Model\VersionProvider $versionProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider,
		\Spameri\Elastic\Model\VersionProvider $versionProvider,
	)
	{
		$this->clientProvider = $clientProvider;
		$this->versionProvider = $versionProvider;
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

			return $this->clientProvider->client()->indices()->getMapping($documentArray);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
