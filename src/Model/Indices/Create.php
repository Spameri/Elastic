<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class Create
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	/**
	 * @param array<mixed> $parameters
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		array $parameters,
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->create(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($parameters),
					)
				)->toArray(),
			)->asArray()
				;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
