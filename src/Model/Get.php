<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Get
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
		private VersionProvider $versionProvider,
	)
	{
	}


	/**
	 * $type parameter is here only for backward compatibility do not use it for new index
	 *
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function execute(
		\Spameri\Elastic\Entity\Property\ElasticId $id,
		string $index,
		string|null $type = NULL,
	): \Spameri\ElasticQuery\Response\ResultSingle
	{
		if ($type === NULL) {
			$type = $index;
		}

		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$type = NULL;
		}

		try {
			$response = $this->clientProvider->client()->get(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						NULL,
						$type,
						$id->value(),
					)
				)
					->toArray(),
			)
			;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapSingleResult($response);
	}

}
