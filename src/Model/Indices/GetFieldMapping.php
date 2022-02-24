<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

class GetFieldMapping
{

	private \Spameri\Elastic\ClientProvider $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function execute(
		string $index,
		string $type = '_doc',
		array $fields = []
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->getFieldMapping(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						NULL,
						$type,
						NULL,
						[
							'fields' => $fields,
						]
					)
				)->toArray()
			)
			;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
