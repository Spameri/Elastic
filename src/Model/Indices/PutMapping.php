<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class PutMapping
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	/**
	 * @param array<mixed> $mapping
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		array $mapping,
		string $dynamic = 'false',
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->putMapping(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain(
						[
							'properties' => $mapping['properties'],
							'dynamic' => $dynamic,
						],
					),
				)
				)->toArray(),
			)->asArray()
				;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
