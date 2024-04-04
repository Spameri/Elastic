<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class Exists
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	public function execute(
		string $index,
	): bool
	{
		try {
			return $this->clientProvider->client()->indices()->exists(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
					)
				)->toArray(),
			)->asBool()
			;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
