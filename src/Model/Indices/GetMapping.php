<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class GetMapping
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
			$documentArray = (
				new \Spameri\ElasticQuery\Document(
					$index,
					null,
				)
			)->toArray();

			return $this->clientProvider->client()->indices()->getMapping($documentArray)->asArray();

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
