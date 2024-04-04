<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class RemoveAlias
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	public function execute(string $alias, string $index): array
	{
		try {
			return $this->clientProvider->client()->indices()->putAlias(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain(
						[
							'actions' => [
								'remove' => [
									'index' => $index,
									'alias' => $alias,
								],
							],
						],
					),
					NULL,
					NULL,
					[
						'name' => $index,
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
