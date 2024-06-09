<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class GetFieldMapping
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	public function execute(
		string $index,
		array $fields = [],
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->getFieldMapping(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						null,
						null,
						[
							'fields' => $fields,
						],
					)
				)->toArray(),
			)->asArray()
			;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
