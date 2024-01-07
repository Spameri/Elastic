<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class GetAllBy
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;

	/**
	 * @var \Spameri\ElasticQuery\Response\ResultMapper
	 */
	private $resultMapper;

	private VersionProvider $versionProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider,
		\Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
		VersionProvider $versionProvider,
	)
	{
		$this->clientProvider = $clientProvider;
		$this->resultMapper = $resultMapper;
		$this->versionProvider = $versionProvider;
	}


	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $options,
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
						new \Spameri\ElasticQuery\Document\Body\Plain($options->toArray()),
						$type,
					)
				)
					->toArray(),
			)->asArray()
			;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapSearchResults($result);
	}

}
