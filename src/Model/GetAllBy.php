<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class GetAllBy
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
	)
	{
	}


	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $options,
		string $index,
	): \Spameri\ElasticQuery\Response\ResultSearch
	{
		try {
			$result = $this->clientProvider->client()->search(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($options->toArray()),
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
