<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

class RemoveAlias
{

	private \Spameri\Elastic\ClientProvider $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function execute(string $alias, string $index): array
	{
		try {
			return $this->clientProvider->client()->indices()->deleteAlias(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					NULL,
					NULL,
					NULL,
					[
						'name' => $alias,
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
