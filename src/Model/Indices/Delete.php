<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class Delete
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	/**
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->delete(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
					)
				)->toArray(),
			)
				;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
