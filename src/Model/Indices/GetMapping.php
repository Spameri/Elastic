<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;


class GetMapping
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
	 * @return array<mixed>
	 */
	public function execute(
		string $index
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
					NULL,
					$type
				)
			)->toArray();

			if (\Spameri\Elastic\Model\VersionProvider::provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
				unset($documentArray['type']);
			}

			return $this->clientProvider->client()->indices()->getMapping($documentArray);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
