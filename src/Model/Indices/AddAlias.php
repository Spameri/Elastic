<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

class AddAlias
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
			try {
				$this->clientProvider->client()->indices()->get(
					(
					new \Spameri\ElasticQuery\Document(
						$alias
					)
					)->toArray()
				);

				throw new \Spameri\Elastic\Exception\AliasAlreadyExists($alias);

			} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $exception) {
				return $this->clientProvider->client()->indices()->putAlias(
					(
						new \Spameri\ElasticQuery\Document(
							$index,
							new \Spameri\ElasticQuery\Document\Body\Plain([
								'actions' => [
									'add' => [
										'index' => $index,
										'alias' => $alias,
									],
								],
							]),
							NULL,
							NULL,
							[
								'name' => $index,
							]
						)
					)->toArray()
				);
			}

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
