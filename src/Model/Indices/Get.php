<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class Get
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
			/** @var array $result */
			$result = $this->clientProvider->client()->indices()->get(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
					)
				)->toArray(),
			)
			;

			return $result;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
