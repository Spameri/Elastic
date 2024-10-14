<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class AddAlias
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	public function execute(string $alias, string $index): array
	{
		try {
			try {
				$this->clientProvider->client()->indices()->get(
					(
					new \Spameri\ElasticQuery\Document(
						$alias,
					)
					)->toArray(),
				)
				;

				throw new \Spameri\Elastic\Exception\AliasAlreadyExists($alias);

			} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
				if ($exception->getCode() !== 404) {
					throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
				}

				return $this->clientProvider->client()->indices()->putAlias(
					(
						new \Spameri\ElasticQuery\Document(
							$index,
							new \Spameri\ElasticQuery\Document\Body\Plain(
								[
									'actions' => [
										'add' => [
											'index' => $index,
											'alias' => $alias,
										],
									],
								],
							),
							null,
							[
								'name' => $index,
							],
						)
					)->toArray(),
				)->asArray()
				;
			}

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
