<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class MoveAlias
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	public function execute(string $alias, string $indexFrom, string $indexTo): array
	{
		try {
			return $this->clientProvider->client()->indices()->putAlias(
				(
				new \Spameri\ElasticQuery\Document(
					$indexFrom,
					new \Spameri\ElasticQuery\Document\Body\Plain(
						[
							'actions' => [
								'remove' => [
									'index' => $indexFrom,
									'alias' => $alias,
								],
								'add' => [
									'index' => $indexTo,
									'alias' => $alias,
								],
							],
						],
					),
					NULL,
					NULL,
					[
						'name' => $indexTo,
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
