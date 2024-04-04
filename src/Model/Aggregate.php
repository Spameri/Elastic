<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Aggregate
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
		private \Spameri\Elastic\Model\VersionProvider $versionProvider,
	)
	{
	}


	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $index,
		string|null $type = NULL,
	): \Spameri\ElasticQuery\Response\ResultSearch
	{
		if ($type === NULL) {
			$type = $index;
		}

		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$type = NULL;
		}

		try {
			$result = $this->clientProvider->client()->search(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain(
						$elasticQuery->toArray(),
					),
					$type,
				)
				)->toArray(),
			)
			;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapSearchResults($result);
	}

}
